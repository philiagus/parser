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

use Philiagus\DataProvider\DataProvider;
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

    public function __construct()
    {
    }

    public function expectSingleCall(
        mixed    $value,
        mixed    $path,
        \Closure $eligible = null,
        mixed    $result = null
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

    public function generate(mixed $subjectValue, array $generatedArgs): \Generator
    {
        $willBeCalled = array_reduce(
            $this->willBeCalledIf,
            fn(bool $carry, \Closure $if) => $carry && $if($subjectValue, $generatedArgs),
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
            function (array $generatedArguments, array $successStack) use ($subjectValue): \Philiagus\Parser\Contract\Parser {
                $parser = new ParserMock();

                foreach ($this->expectedSingleCalls as $index => ['value' => $valueOrClosure,
                         'path' => $subjectClassOrClosure,
                         'eligible' => $eligible,
                         'result' => $resultClosure]) {
                    if (!$eligible($subjectValue, $generatedArguments, $successStack)) continue;

                    $valueOrClosure = $valueOrClosure($subjectValue, $generatedArguments, $successStack);
                    $subjectClassOrClosure = $subjectClassOrClosure($subjectValue, $generatedArguments, $successStack);
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
                    $valueOrClosures = $valueOrClosure($subjectValue, $generatedArguments, $successStack);

                    $subjectClassOrClosure = $subjectClassOrClosure($subjectValue, $generatedArguments, $successStack);

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
            function (array $generatedArguments, array $successStack, ErrorCollection $errorCollection) use ($subjectValue): \Philiagus\Parser\Contract\Parser {
                $parser = new ParserMock();

                $parser->expect(
                    fn() => true,
                    fn() => true,
                    static function (Subject $subject) use ($errorCollection) {
                        $message = uniqid(microtime());
                        $error = new Error($subject, $message);
                        $errorCollection->add($error->getMessage());
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
}
