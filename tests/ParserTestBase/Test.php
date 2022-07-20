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
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class Test
{
    /** @var Argument[] */
    private array $methodArgs = [];

    /** @var Argument[] */
    private array $constructorArgs = [];

    /** @var array<string, \Closure> */
    private array $success = [];

    private ?self $then = null;
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
            'result' => $successValidator ?? function (Subject $start, Result $result): array {
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
            'source' => static fn() => (new DataProvider($flags))->provide(false),
            'success' => static fn() => true,
            'result' => static function (Subject $start, Result $result): array {
                if (!DataProvider::isSame($start->getValue(), $result->getValue())) {
                    return ['Result has been altered from ' . Debug::stringify($start->getValue()) . ' to ' . Debug::stringify($result->getValue())];
                }

                return [];
            },
        ];

        return $this;
    }

    public function then(string $method): self
    {
        return $this->then = new self(fn() => null, $method);
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
            'result' => $successValidator ?? static function (Subject $start, Result $result): array {
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
            'result' => $successValidator ?? function (Subject $start, Result $result): array {
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
        $this->expectError[] = $errorStringGenerator;

        return $this;
    }

    private function runThroughArgStack(
        mixed  $value,
        array  $generatedArgs,
        array  $successArgs,
        string $carryoverName = '',
        int    $internalOffset = 0,
        bool $inSuccess = true
    ): array
    {
        $allArgs = $this->methodArgs;
        if (!isset($allArgs[$internalOffset])) {
            return [
                $carryoverName => [$inSuccess, $generatedArgs, $successArgs],
            ];
        }
        $result = [];
        $errorMeansFailure = $allArgs[$internalOffset]->errorMeansFailure();
        foreach ($allArgs[$internalOffset]->generate($value, $generatedArgs, $successArgs) as $subName => [$isSuccess, $argument]) {
            if ($errorMeansFailure && !$inSuccess && !$isSuccess) continue;
            foreach ($this->runThroughArgStack(
                $value,
                [...$generatedArgs, $argument],
                [...$successArgs, $isSuccess],
                $carryoverName . ' | ' . $subName,
                $internalOffset + 1,
                $inSuccess && ($isSuccess || !$errorMeansFailure)
            ) as $name => $forward) {
                $result[$name] = $forward;
            }
        }

        return $result;
    }

    public function generate(
        ?Parser $handedOverParser = null,
        array   $handedOverArguments = [],
        array   $handedOverSuccesses = []
    ): \Generator
    {
        $handedOverCount = count($handedOverArguments);
        foreach ($this->success as $line => $calls) {
            foreach ($calls as $callIndex => ['source' => $sourceGenerator, 'success' => $successCallback, 'result' => $resultValidator]) {
                foreach ($sourceGenerator() as $name => $value) {
                    $isSuccess = $successCallback($value);
                    foreach (
                        $this->runThroughArgStack(
                            $value,
                            generatedArgs: $handedOverArguments,
                            successArgs: $handedOverSuccesses
                        ) as $argsCase => [$success, $args, $successStack]
                    ) {
                        $success = $isSuccess && $success;
                        foreach (['throw' => true, 'nothrow' => false] as $throwLabel => $throw) {
                            $errorCollection = new ErrorCollection();
                            if(!$success) {
                                foreach($this->expectError as $expectError) {
                                    $errorCollection->add($expectError($value));
                                }
                            }
                            $caseName = $line . ' #' . $callIndex . ' [' . Debug::stringify($value) . '] ' .
                                $name . ' >> ' . $argsCase . ' >> ' .
                                ($success ? 'success' : 'error') . ' ' . $throwLabel;

                            $realArgs = [];
                            foreach ($args as $arg) {
                                $realArgs[] = $arg instanceof \Closure ? $arg($realArgs, $successStack, $errorCollection) : $arg;
                            }
                            $methodArgs = array_slice($realArgs, $handedOverCount);
                            yield $caseName => new TestCase(
                                $success,
                                $throw,
                                Subject::default($value, $throw),
                                function () use ($value, $methodArgs, $handedOverParser): Parser {
                                    $parser = $handedOverParser ?? ($this->parserCreation)($value, $this->method !== null ? [] : $methodArgs);
                                    if ($this->method) {
                                        $parser->{$this->method}(...$methodArgs);
                                    }

                                    return $parser;
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
                                $errorCollection,
                                $realArgs,
                                $successStack,
                                $this->then
                            );
                        }
                    }
                }
            }
        }
    }
}
