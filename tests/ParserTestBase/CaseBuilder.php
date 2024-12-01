<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\ParserTestBase;

use Philiagus\DataProvider\DataProvider;
use PHPUnit\Framework\Assert;

class CaseBuilder
{

    public static int $totalNumberCreated = 0;

    /** @var TestInstance[] */
    private array $tests = [];

    public function __construct(
        private readonly string $coveredClass
    )
    {
        self::$totalNumberCreated++;
    }

    public function test(?\Closure $parserCreation = null, ?string $methodName = null): TestInstance
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $targetClassName = $this->getTargetClassName();
        $targetClass = new \ReflectionClass($targetClassName);
        if ($parserCreation === null) {
            if (
                !$targetClass->hasMethod('new') ||
                !$targetClass->getMethod('new')->isStatic() ||
                !empty($targetClass->getMethod('new')->getAttributes())) {
                Assert::fail("Static new does not exist for class {$targetClassName}");
            }
            $parserCreation = static fn() => $targetClassName::new();
        }

        if (func_num_args() < 2) {
            if (!preg_match('~^test_?(?<method>.++)$~', $trace[1]['function'], $matches))
                Assert::fail('Cannot extract method name');

            $methodName = lcfirst($matches['method']);
        }

        return $this->tests[] = new TestInstance($parserCreation, $methodName);
    }

    private function getTargetClassName(): string
    {
        return $this->coveredClass;
    }

    public function parserArgument(): Argument\Parser
    {
        return new Argument\Parser();
    }

    public function evaluatedArgument(): Argument\Evaluated
    {
        return new Argument\Evaluated();
    }

    public function messageArgument(): Argument\Message
    {
        return new Argument\Message();
    }

    public function run(): void
    {
        $results = [];
        $hasErrors = [];
        foreach ($this->tests as $test) {
            foreach ($test->generate() as $name => $case) {
                Assert::assertEquals(true, true);
                $result = $case->run();
                if ($result['hasError']) $hasErrors = true;
                $results[$name] = $result;
            }
        }

        if ($hasErrors) {
            $string = PHP_EOL . implode(PHP_EOL, $this->generateLines($results));
            Assert::fail($string);
        }
    }

    private function generateLines(array $results, int $indent = 0): array
    {
        $indentChar = str_repeat("\t", $indent);
        $indentedChar = str_repeat("\t", $indent + 1);
        $successLines = [];
        $errorLines = [];
        foreach ($results as $name => $result) {
            $errors = $result['errors'] ?? [];
            if (!$result['hasError']) {
                $successLines[] = $indentChar . '✔ ' . $name;
                continue;
            }

            $errorLines[] = $indentChar . '❌ ' . $name;
            foreach ($errors as $error) {
                $errorLines[] = $indentedChar . "- $error";
            }
        }

        return [...$errorLines, ...$successLines];
    }

    public function fixedArgument(mixed ...$arguments): Argument\Fixed
    {
        $result = new Argument\Fixed();
        foreach ($arguments as $arg) {
            $result->success($arg);
        }
        return $result;
    }

    public function dataProviderArgument(int $flags = DataProvider::TYPE_ALL): Argument\Generated
    {
        return new Argument\Generated($flags);
    }

    public function testStaticConstructor(): TestInstance
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $targetClassName = $this->getTargetClassName();
        $targetClass = new \ReflectionClass($targetClassName);


        if (!preg_match('~^test_?(?<method>.++)$~', $trace[1]['function'], $matches)) {
            Assert::fail('Cannot extract method name');
        }

        $methodName = lcfirst($matches['method']);
        if (
            !$targetClass->hasMethod($methodName) ||
            !$targetClass->getMethod($methodName)->isStatic()
        ) {
            Assert::fail("Static method $methodName does not exist for class {$matches['class']}");
        }
        $parserCreation = static fn($value, array $args) => $targetClassName::$methodName(...$args);

        return $this->tests[] = new TestInstance($parserCreation, null);
    }

}
