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

namespace Philiagus\Parser\Test\Unit\Parser\Logic;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Parser\Logic\OneOf;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\ParserTestBase;

/**
 * @covers \Philiagus\Parser\Parser\Logic\OneOf
 */
class OneOfTest extends ParserTestBase
{
    use ChainableParserTestTrait;

    public function testNullOr(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Subject::class
                    )
                    ->errorWillBeHidden()
                    ->willBeCalledIf(fn($value) => $value !== null)
            )
            ->expectError(
                fn() => 'Provided value does not match any of the expected formats or values'
            )
            ->provider(
                DataProvider::TYPE_ALL
            );

        $builder->run();
    }

    public function testSameAs(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => !$value), // make first argument not match
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value, fn($value) => $value === $value)
                    ->error(fn($value) => $value, fn($value) => $value !== $value)
                    ->error(fn($value) => !$value)
                    ->error(fn($value) => clone $value, function ($value) {
                        try {
                            /** @noinspection PhpExpressionResultUnusedInspection */
                            clone $value;

                            return true;
                        } catch (\Throwable) {
                            return false;
                        }
                    })
            )
            ->expectError(
                fn() => 'Provided value does not match any of the expected formats or values'
            )
            ->provider(DataProvider::TYPE_ALL);

        $builder->run();
    }

    public function testEqualTo(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => !$value), // make first argument not match
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value, fn($value) => $value == $value)
                    ->error(fn($value) => $value, fn($value) => $value != $value)
                    ->error(fn($value) => !$value)
                    ->success(fn($value) => clone $value, function ($value) {
                        try {
                            /** @noinspection PhpExpressionResultUnusedInspection */
                            clone $value;

                            return true;
                        } catch (\Throwable) {
                            return false;
                        }
                    })
            )
            ->expectError(
                fn() => 'Provided value does not match any of the expected formats or values'
            )
            ->provider(DataProvider::TYPE_ALL);

        $builder->run();
    }

    public function testParser(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Subject::class
                    )
                    ->errorDoesNotMeanFail()
                    ->errorWillBeHidden(),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Subject::class
                    )
                    ->errorWillBeHidden()
                    ->willBeCalledIf(fn($_1, $_2, array $successes) => !$successes[0]),
            )
            ->expectError(
                fn() => 'Provided value does not match any of the expected formats or values'
            )
            ->provider(DataProvider::TYPE_ALL);

        $builder->run();
    }

    public function testSetNonOfErrorMessage(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->messageArgument()
            )
            ->provider(
                DataProvider::TYPE_ALL,
                expectSuccess: fn() => false
            );

        $builder->run();
    }

    public function testSetDefaultResult(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => !$value)
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Contract\Subject $subject, Contract\Result $result, array $generatedArguments) {
                    if (!DataProvider::isSame($generatedArguments[0], $result->getValue())) {
                        return ['Result value does not match'];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => $value == $value)
            ->map(static fn($value) => [$value, fn($value) => OneOf::new()->equalTo($value), $value])
            ->provide(false);
    }
}
