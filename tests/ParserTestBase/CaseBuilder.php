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

        if ($methodName === null) {
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
        $errors = [];
        $hasErrors = [];
        foreach ($this->tests as $test) {
            foreach ($test->generate() as $name => $case) {
                $result = $case->run();
                if ($result) $hasErrors = true;
                $errors[$name] = $result;
            }
        }

        Assert::assertEquals(true, true);
        if ($hasErrors) {
            $string = '';
            foreach ($errors as $name => $subErrors) {
                $string .= PHP_EOL . $name;
                if (empty($subErrors)) {
                    $string .= ' => ✔';
                    continue;
                }

                $string .= ' => ❌';
                $string .= PHP_EOL;
                foreach ($subErrors as $subError) {
                    $string .= "\t- $subError" . PHP_EOL;
                }
            }
            Assert::fail($string);
        }
    }

    public function reveal(): array
    {
        $cases = [];
        foreach ($this->tests as $index => $test) {
            foreach ($test->generate() as $name => $case) {
                $cases[$test->method . ' #' . $index . ' -> ' . $name] = $case;
            }
        }

        return $cases;
    }

    public function fixedArgument(): Argument\Fixed
    {
        return new Argument\Fixed();
    }

    public function generatedArgument(int $flags = DataProvider::TYPE_ALL): Argument\Generated
    {
        return new Argument\Generated($flags);
    }

}
