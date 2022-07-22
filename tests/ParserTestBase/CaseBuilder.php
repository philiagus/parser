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

    /** @var Test[] */
    private array $tests = [];

    public function __construct()
    {

    }

    public function test(?\Closure $parserCreation = null, ?string $methodName = null): Test
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $reflection = new \ReflectionClass($trace[1]['class']);
        if (!preg_match('~@covers\s++(?<class>\S++)~', $reflection->getDocComment() ?: '', $matches)) {
            Assert::fail("Class {$trace[0]['class']} does not define a @covers");
        }
        $targetClassName = $matches['class'];
        $targetClass = new \ReflectionClass($matches['class']);
        if ($parserCreation === null) {
            if (
                !$targetClass->hasMethod('new') ||
                !$targetClass->getMethod('new')->isStatic() ||
                !empty($targetClass->getMethod('new')->getAttributes())) {
                Assert::fail("Static new does not exist for class {$matches['class']}");
            }
            $parserCreation = fn() => $targetClassName::new();
        }

        if (func_num_args() < 2) {
            if (!preg_match('~^test_?(?<method>.++)$~', $trace[1]['function'], $matches)) {
                Assert::fail('Cannot extract method name');
            }

            $methodName = lcfirst($matches['method']);
        }

        return $this->tests[] = new Test($parserCreation, $methodName);
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
            $string = PHP_EOL . implode(PHP_EOL, $this->genereateLines($results));
            Assert::fail($string);
        }
    }

    private function genereateLines(array $results, int $indent = 0): array
    {
        $return = [];
        $indentChar = str_repeat("\t", $indent);
        $indentedChar = str_repeat("\t", $indent + 1);
        foreach ($results as $name => $result) {
            $errors = $result['errors'] ?? [];
            if (!$result['hasError']) {
                $return[] = $indentChar . '✔ ' . $name;
                continue;
            }

            $return[] = $indentChar . '❌ ' . $name;
            foreach ($errors as $error) {
                $return[] = $indentedChar . "- $error";
            }
        }
        return $return;
    }

    public function fixedArgument(): Argument\Fixed
    {
        return new Argument\Fixed();
    }

    public function dataProviderArgument(int $flags = DataProvider::TYPE_ALL): Argument\Generated
    {
        return new Argument\Generated($flags);
    }

    public function testStaticConstructor(): Test
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $reflection = new \ReflectionClass($trace[1]['class']);
        if (!preg_match('~@covers\s++(?<class>\S++)~', $reflection->getDocComment() ?: '', $matches)) {
            Assert::fail("Class {$trace[1]['class']} does not define a @covers");
        }
        $targetClassName = $matches['class'];
        $targetClass = new \ReflectionClass($matches['class']);


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
        $parserCreation = fn($value, array $args) => $targetClassName::$methodName(...$args);

        return $this->tests[] = new Test($parserCreation, null);
    }

}
