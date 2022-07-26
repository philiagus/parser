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

namespace Philiagus\Parser\Test\ParserTestBase\Argument;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Test\Mock\ParserMock;
use Philiagus\Parser\Test\ParserTestBase\Argument;
use Philiagus\Parser\Test\ParserTestBase\ErrorCollection;

class Parser implements Argument
{
    private array $expectedSingleCalls = [];
    private array $expectedMultipleCalls = [];
    private array $willBeCalledIf = [];

    private bool $errorWillBeHidden = false;
    private bool $errorIsFail = true;
    private string $errorMessageOnError;

    public function __construct()
    {
    }

    public function errorWillBeHidden(): self
    {
        $this->errorWillBeHidden = true;

        return $this;
    }

    public function expectSingleCall(
        mixed    $value,
        mixed    $path,
        \Closure $eligible = null,
        mixed    $result = null,
    ): self
    {
        $this->expectedSingleCalls[] = [
            'value' => $value instanceof \Closure ? $value : fn() => $value,
            'path' => $path instanceof \Closure ? $path : fn() => $path,
            'eligible' => $eligible ?? fn() => true,
            'result' => $result,
        ];

        return $this;
    }

    public function expectMultipleCalls(
        mixed    $values,
        mixed    $path,
        \Closure $eligible = null,
        mixed    $result = null
    ): self
    {
        $this->expectedMultipleCalls[] = [
            'values' => $values instanceof \Closure ? $values : fn() => $values,
            'paths' => $path instanceof \Closure ? $path : fn() => $path,
            'eligible' => $eligible ?? fn() => true,
            'result' => $result,
        ];

        return $this;
    }

    public function generate(mixed $subjectValue, array $generatedArgs, array $successes): \Generator
    {
        $willBeCalled = array_reduce(
            $this->willBeCalledIf,
            fn(bool $carry, \Closure $if) => $carry && $if($subjectValue, array_map(
                    fn($arg) => $arg instanceof \Closure ? $arg($generatedArgs, $successes) : $arg,
                    $generatedArgs
                ), $successes),
            true
        );
        if (!$willBeCalled) {
            yield 'uncalled parser' => [
                true,
                function () use ($subjectValue): \Philiagus\Parser\Contract\Parser {
                    return new ParserMock();
                },
            ];

            return;
        }

        yield 'parser success' => [
            true,
            function (array $evaluatedArguments, array $successStack) use ($subjectValue): \Philiagus\Parser\Contract\Parser {
                $parser = new ParserMock();

                foreach ($this->expectedSingleCalls as $index => ['value' => $valueOrClosure,
                         'path' => $subjectClassOrClosure,
                         'eligible' => $eligible,
                         'result' => $resultClosure]) {
                    if (!$eligible($subjectValue, $evaluatedArguments, $successStack)) continue;

                    $valueOrClosure = $valueOrClosure($subjectValue, $evaluatedArguments, $successStack);
                    $subjectClassOrClosure = $subjectClassOrClosure($subjectValue, $evaluatedArguments, $successStack);
                    $parser
                        ->expect(
                            $valueOrClosure,
                            $subjectClassOrClosure,
                            $resultClosure
                        );
                }

                foreach ($this->expectedMultipleCalls as $index => ['values' => $valueOrClosure,
                         'paths' => $subjectClassOrClosure,
                         'eligible' => $eligible,
                         'result' => $resultClosure]) {
                    $valueOrClosures = $valueOrClosure($subjectValue, $evaluatedArguments, $successStack);

                    $subjectClassOrClosure = $subjectClassOrClosure($subjectValue, $evaluatedArguments, $successStack);

                    foreach ($valueOrClosures as $valueOrClosure) {
                        $parser
                            ->expect(
                                $valueOrClosure,
                                $subjectClassOrClosure,
                                $resultClosure
                            );
                    }
                }

                return $parser;
            }];
        yield 'parser error' => [
            false,
            function (array $generatedArguments, array $successStack, ErrorCollection $errorCollection = null) use ($subjectValue): \Philiagus\Parser\Contract\Parser {
                $parser = new ParserMock();

                if (isset($this->errorMessageOnError)) {
                    $errorCollection?->add($this->errorMessageOnError);
                }

                $parser->expect(
                    fn() => true,
                    fn() => true,
                    function (\Philiagus\Parser\Contract\Subject $subject) use ($errorCollection) {
                        $message = uniqid(microtime());
                        $error = new Error($subject, $message);
                        if (!$this->errorWillBeHidden) {
                            $errorCollection->add($error->getMessage());
                        }
                        if ($subject->throwOnError()) {
                            throw new ParsingException($error);
                        }

                        return new Result($subject, null, [$error]);
                    },
                    INF
                );

                return $parser;
            }];
    }

    public function willBeCalledIf(\Closure $condition): self
    {
        $this->willBeCalledIf[] = $condition;

        return $this;
    }

    public function errorDoesNotMeanFail(): self
    {
        $this->errorIsFail = false;

        return $this;
    }

    public function getErrorMeansFail(): bool
    {
        return $this->errorIsFail;
    }

    public function expectErrorMessageOnError(string $string): self
    {
        $this->errorMessageOnError = $string;

        return $this;
    }
}
