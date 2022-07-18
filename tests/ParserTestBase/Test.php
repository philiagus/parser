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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class Test
{
    /** @var Argument[] */
    private array $args = [];

    /** @var array<string, \Closure> */
    private array $success = [];

    public function __construct(
        private readonly \Closure $parserCreation,
        public readonly string    $method
    )
    {
    }

    public function arguments(Argument ...$arguments): self
    {
        $this->args = [...$this->args, ...$arguments];

        return $this;
    }

    public function values(array $values, ?\Closure $expectSuccess = null): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->success[$trace['line']][] = [
            'source' => fn() => $values,
            'success' => $expectSuccess ?? fn() => true,
            'result' => function (Subject $start, Result $result): array {
                if (!DataProvider::isSame($start->getValue(), $result->getValue())) {
                    return ['Result has been altered from ' . Debug::stringify($start->getValue()) . ' to ' . Debug::stringify($result->getValue())];
                }

                return [];
            },
        ];

        return $this;
    }

    public function successProvider(int $flags): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->success[$trace['line']][] = [
            'source' => fn() => (new DataProvider($flags))->provide(false),
            'success' => fn() => true,
            'result' => function (Subject $start, Result $result): array {
                if (!DataProvider::isSame($start->getValue(), $result->getValue())) {
                    return ['Result has been altered from ' . Debug::stringify($start->getValue()) . ' to ' . Debug::stringify($result->getValue())];
                }

                return [];
            },
        ];

        return $this;
    }

    public function provider(int $flags, \Closure $expectSuccess = null): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->success[$trace['line']][] = [
            'source' => fn() => (new DataProvider($flags))->provide(false),
            'success' => $expectSuccess ?? fn() => true,
            'result' => function (Subject $start, Result $result): array {
                if (!DataProvider::isSame($start->getValue(), $result->getValue())) {
                    return ['Result has been altered from ' . Debug::stringify($start->getValue()) . ' to ' . Debug::stringify($result->getValue())];
                }

                return [];
            },
        ];

        return $this;
    }

    private function runThroughArgStack(mixed $value, string $carryoverName = '', array $generatedArgs = [], int $offset = 0, array $successArgs = []): array
    {
        $inSuccess = !in_array(false, $successArgs);
        if (!isset($this->args[$offset])) {
            return [
                $carryoverName => [$inSuccess, $generatedArgs, $successArgs],
            ];
        }
        $result = [];
        foreach ($this->args[$offset]->generate($value) as $subName => [$isSuccess, $argument]) {
            if (!$inSuccess && !$isSuccess) continue;
            foreach ($this->runThroughArgStack(
                $value,
                $carryoverName . ' | ' . $subName,
                [...$generatedArgs, $argument],
                $offset + 1,
                [...$successArgs, $isSuccess]
            ) as $name => $forward) {
                $result[$name] = $forward;
            }
        }

        return $result;
    }

    public function generate(): \Generator
    {
        foreach ($this->success as $line => $calls) {
            foreach ($calls as $callIndex => ['source' => $sourceGenerator, 'success' => $successCallback, 'result' => $resultValidator]) {
                foreach ($sourceGenerator() as $name => $value) {
                    $isSuccess = $successCallback($value);
                    foreach ($this->runThroughArgStack($value) as $argsCase => [$success, $args, $successStack]) {
                        $success = $isSuccess && $success;
                        foreach (['throw' => true, 'nothrow' => false] as $throwLabel => $throw) {
                            $errorCollection = new ErrorCollection();
                            $caseName = $line . ' #' . $callIndex . ' [' . Debug::stringify($value) . '] ' .
                                $name . ' >> ' . $argsCase . ' >> ' .
                                ($success ? 'success' : 'error') . ' ' . $throwLabel;

                            yield $caseName => new TestCase(
                                $success,
                                $throw,
                                Subject::default($value, $throw),
                                function () use ($args, $value, $successStack, $errorCollection) {
                                    $realArgs = [];
                                    foreach ($args as $arg) {
                                        $realArgs[] = $arg instanceof \Closure ? $arg($realArgs, $successStack, $errorCollection) : $arg;
                                    }

                                    return ($this->parserCreation)($value)
                                        ->{$this->method}(...$realArgs);
                                },
                                $success ? $resultValidator : function (Subject $subject, Result $result): array {
                                    $errors = [];
                                    if ($result->isSuccess()) {
                                        $errors[] = 'Should be a success, but is error';
                                    }
                                    if (empty($result->getErrors())) {
                                        $errors[] = 'Errors should not be empty';
                                    }

                                    return $errors;
                                },
                                $errorCollection
                            );
                        }
                    }
                }
            }
        }
    }
}
