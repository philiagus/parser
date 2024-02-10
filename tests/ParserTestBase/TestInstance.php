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
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Util\Debug;

class TestInstance
{
    /** @var Argument[] */
    private array $methodArgs = [];

    /** @var array{string, Argument[]} */
    private array $calls = [];

    /** @var array<string, \Closure> */
    private array $success = [];

    /** @var array<\Closure> */
    private array $expectError = [];

    public function __construct(
        private readonly \Closure $parserCreation,
        public readonly ?string   $method
    )
    {
    }

    public function arguments(Argument ...$arguments): self
    {
        $this->methodArgs = [...$this->methodArgs, ...$arguments];

        return $this;
    }

    public function call(string $method, Argument ...$args): self
    {
        $this->calls[] = [$method, $args];

        return $this;
    }

    public function values(
        array     $values,
        ?\Closure $expectSuccess = null,
        ?\Closure $successValidator = null
    ): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->success[$trace['line']][] = [
            'source' => fn() => $values,
            'success' => $expectSuccess ?? fn() => true,
            'result' => $successValidator ?? function (Contract\Subject $start, Contract\Result $result): array {
                    if (!DataProvider::isSame($start->getValue(), $result->getValue())) {
                        return ['Result has been altered from ' . Debug::stringify($start->getValue()) . ' to ' . Debug::stringify($result->getValue())];
                    }

                    return [];
                },
        ];

        return $this;
    }

    public function successProvider(
        int $flags,
        \Closure $resultAssertion = null
    ): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->success[$trace['line']][] = [
            'source' => static fn() => (new DataProvider($flags))->provide(false),
            'success' => static fn() => true,
            'result' => static function (Contract\Subject $start, Contract\Result $result) use ($resultAssertion): array {
            $startValue = $start->getValue();
                $resultValue = $result->getValue();
                if ($resultAssertion) {
                    if (!$resultAssertion($startValue, $resultValue)) {
                        return ['Result ' . Debug::stringify($resultValue) . ' (create from ' . Debug::stringify($startValue) . ') did not match expectation'];
                    }
                } elseif (!DataProvider::isSame($startValue, $resultValue)) {
                    return ['Result has been altered from ' . Debug::stringify($startValue) . ' to ' . Debug::stringify($resultValue)];
                }

                return [];
            },
        ];

        return $this;
    }

    public function provider(
        int       $flags,
        \Closure  $expectSuccess = null,
        ?\Closure $successValidator = null
    ): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->success[$trace['line']][] = [
            'source' => static fn() => (new DataProvider($flags))->provide(false),
            'success' => $expectSuccess ?? static fn() => true,
            'result' => $successValidator ?? static function (Contract\Subject $start, Contract\Result $result): array {
                    if (!DataProvider::isSame($start->getValue(), $result->getValue())) {
                        return ['Result has been altered from ' . Debug::stringify($start->getValue()) . ' to ' . Debug::stringify($result->getValue())];
                    }

                    return [];
                },
        ];

        return $this;
    }

    public function value(
        mixed     $value,
        ?\Closure $expectSuccess = null,
        ?\Closure $successValidator = null
    ): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->success[$trace['line']][] = [
            'source' => fn() => [$value],
            'success' => $expectSuccess ?? fn() => true,
            'result' => $successValidator ?? function (Contract\Subject $start, Contract\Result $result): array {
                    if (!DataProvider::isSame($start->getValue(), $result->getValue())) {
                        return ['Result has been altered from ' . Debug::stringify($start->getValue()) . ' to ' . Debug::stringify($result->getValue())];
                    }

                    return [];
                },
        ];

        return $this;
    }

    public function expectError(\Closure $errorStringGenerator): self
    {
        $this->expectError[] = ['=', $errorStringGenerator];

        return $this;
    }

    public function expectErrorRegex(\Closure $regex): self
    {
        $this->expectError[] = ['regex', $regex];

        return $this;
    }

    private function runThroughArgStack(
        mixed  $value,
        array  $generatedArgs = [],
        array  $successArgs = [],
        string $carryoverName = '',
        int    $internalOffset = 0,
        bool   $inSuccess = true,
        ?array $allArgs = null
    ): array
    {
        if (empty($allArgs)) {
            $allArgs = $this->methodArgs;
            foreach ($this->calls as [$method, $args]) {
                $allArgs = [...$allArgs, ...$args];
            }
        }
        if (!isset($allArgs[$internalOffset])) {
            return [
                $carryoverName => [$inSuccess, $generatedArgs, $successArgs],
            ];
        }
        $result = [];
        $errorMeansFailure = $allArgs[$internalOffset]->getErrorMeansFail();
        foreach ($allArgs[$internalOffset]->generate($value, $generatedArgs, $successArgs) as $subName => [$isSuccess, $argument]) {
            if ($errorMeansFailure && !$inSuccess && !$isSuccess) continue;
            foreach ($this->runThroughArgStack(
                $value,
                [...$generatedArgs, $argument],
                [...$successArgs, $isSuccess],
                $carryoverName . ' | ' . $subName,
                $internalOffset + 1,
                $inSuccess && ($isSuccess || !$errorMeansFailure),
                $allArgs
            ) as $name => $forward) {
                $result[$name] = $forward;
            }
        }

        return $result;
    }

    public function generate(
        ?Parser $handedOverParser = null
    ): \Generator
    {
        foreach ($this->success as $line => $calls) {
            foreach ($calls as $callIndex => ['source' => $sourceGenerator, 'success' => $successCallback, 'result' => $resultValidator]) {
                foreach ($sourceGenerator() as $name => $value) {
                    $isSuccess = $successCallback($value);
                    foreach (
                        $this->runThroughArgStack($value) as $argsCase => [$success, $args, $successStack]
                    ) {
                        $success = $isSuccess && $success;
                        foreach (['throw' => true, 'nothrow' => false] as $throwLabel => $throw) {
                            $errorCollection = new ErrorCollection();
                            if (!$success) {
                                foreach ($this->expectError as [$compareType, $expectError]) {
                                    $errorCollection->add($compareType, $expectError($value));
                                }
                            }
                            $caseName = $line . ' #' . $callIndex . ' [' . Debug::stringify($value) . '] ' .
                                $name . ' >> ' . $argsCase . ' >> ' .
                                ($success ? 'success' : 'error') . ' ' . $throwLabel;

                            $realArgs = [];
                            foreach ($args as $arg) {
                                $realArgs[] = $arg instanceof \Closure ? $arg($realArgs, $successStack, $errorCollection) : $arg;
                            }

                            $argumentCounts = [];
                            $methodCalls = [];
                            if ($this->method !== null) {
                                $argumentCounts[] = 0;
                                $methodCalls[] = $this->method;
                            }
                            $argumentCounts[] = count($this->methodArgs);

                            foreach ($this->calls as [$method, $arguments]) {
                                $methodCalls[] = $method;
                                $argumentCounts[] = count($arguments);
                            }
                            $methodArgs = $this->splitArray($realArgs, $argumentCounts);
                            yield $caseName => new TestCase(
                                $success,
                                $throw,
                                Subject::default($value, throwOnError: $throw),
                                function () use ($value, $methodArgs, $handedOverParser, $methodCalls): Parser {
                                    $parser = $handedOverParser ?? ($this->parserCreation)($value, array_shift($methodArgs));
                                    foreach ($methodCalls as $method) {
                                        $parser->{$method}(...array_shift($methodArgs));
                                    }

                                    return $parser;
                                },
                                $success ? $resultValidator : function (Contract\Subject $subject, Contract\Result $result): array {
                                    $errors = [];
                                    if ($result->isSuccess()) $errors[] = 'Should be a success, but is error';
                                    if (empty($result->getErrors())) $errors[] = 'Errors should not be empty';

                                    return $errors;
                                },
                                $errorCollection,
                                $realArgs
                            );
                        }
                    }
                }
            }
        }
    }

    private function splitArray(array $source, array $slices): array
    {
        $result = [];
        $offset = 0;
        foreach ($slices as $slice) {
            $result[] = array_slice($source, $offset, $slice);
            $offset += $slice;
        }

        return $result;
    }
}
