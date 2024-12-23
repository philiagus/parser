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
use Philiagus\Parser\Parser\Logic\Conditional;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Utility\Forwarded;
use Philiagus\Parser\Subject\Utility\Test;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Conditional::class)]
class ConditionalTest extends ParserTestBase
{

    use ChainableParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(static fn($value) => [$value, fn() => Conditional::new()->setDefaultResult($value), $value])
            ->provide(false);
    }

    public function testSetNonOfErrorMessage(): void
    {
        $builder = $this->builder();

        $builder
            ->test()
            ->arguments(
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn() => true)
            )
            ->provider(
                DataProvider::TYPE_ALL,
                fn() => false
            );

        $builder->run();
    }

    public function testIfSameAs(): void
    {
        $expectedResult = new \stdClass();
        $builder = $this->builder();

        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->expectErrorMessageOnError('Provided value does not match any of the expected formats or values')
                    ->success(fn($value) => $value, fn($value) => $value === $value)
                    ->error(fn($value) => $value, fn($value) => $value !== $value)
                    ->error(fn($value) => !$value),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Forwarded::class,
                        result: fn(Subject $subject) => new Result($subject, $expectedResult, [])
                    )
                    ->willBeCalledIf(fn($_1, $_2, array $successes) => $successes[0])
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) use ($expectedResult) {
                    if ($expectedResult !== $result->getValue()) {
                        return ['value does not match'];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function testIfSameAsListElement(): void
    {
        $expectedResult = new \stdClass();
        $builder = $this->builder();

        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->expectErrorMessageOnError('Provided value does not match any of the expected formats or values')
                    ->success(fn($value) => [!$value, $value, is_object($value) ? 1 : new \stdClass()], fn($value) => $value === $value)
                    ->error(fn($value) => [$value], fn($value) => $value !== $value)
                    ->error(fn($value) => [!$value, NAN]),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Forwarded::class,
                        result: fn(Subject $subject) => new Result($subject, $expectedResult, [])
                    )
                    ->willBeCalledIf(fn($_1, $_2, array $successes) => $successes[0])
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) use ($expectedResult) {
                    if ($expectedResult !== $result->getValue()) {
                        return ['value does not match'];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function testIfEqualTo(): void
    {
        $expectedResult = new \stdClass();
        $builder = $this->builder();

        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->expectErrorMessageOnError('Provided value does not match any of the expected formats or values')
                    ->success(fn($value) => $value, fn($value) => $value == $value)
                    ->error(fn($value) => $value, fn($value) => $value != $value)
                    ->error(fn($value) => !$value),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Forwarded::class,
                        result: fn(Subject $subject) => new Result($subject, $expectedResult, [])
                    )
                    ->willBeCalledIf(fn($_1, $_2, array $successes) => $successes[0])
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) use ($expectedResult) {
                    if ($expectedResult !== $result->getValue()) {
                        return ['value does not match'];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function testIfEqualToListElement(): void
    {
        $expectedResult = new \stdClass();
        $builder = $this->builder();

        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->expectErrorMessageOnError('Provided value does not match any of the expected formats or values')
                    ->success(fn($value) => [$value], fn($value) => $value == $value)
                    ->error(fn($value) => [$value, NAN], fn($value) => $value != $value, 'not equal to self')
                    ->error(fn($value) => [!$value], description: 'not equal to not self'),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Forwarded::class,
                        result: fn(Subject $subject) => new Result($subject, $expectedResult, [])
                    )
                    ->willBeCalledIf(fn($_1, $_2, array $successes) => $successes[0])
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) use ($expectedResult) {
                    if ($expectedResult !== $result->getValue()) {
                        return ['value does not match'];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function testIfParser(): void
    {
        $expectedResult = new \stdClass();
        $builder = $this->builder();

        // no pipe
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->errorWillBeHidden()
                    ->expectErrorMessageOnError('Provided value does not match any of the expected formats or values')
                    ->expectSingleCall(
                        fn($value) => $value,
                        Test::class,
                        result: fn(Subject $subject) => new Result($subject, new \stdClass(), [])
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Forwarded::class,
                        result: fn(Subject $subject) => new Result($subject, $expectedResult, [])
                    )
                    ->willBeCalledIf(fn($_1, $_2, array $successes) => $successes[0])
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) use ($expectedResult) {
                    if ($expectedResult !== $result->getValue()) {
                        return ['value does not match'];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function testIfParserPiped(): void
    {
        $expectedResult = new \stdClass();
        $builder = $this->builder();

        // pipe
        $forwardedValue = new \stdClass();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->errorWillBeHidden()
                    ->expectErrorMessageOnError('Provided value does not match any of the expected formats or values')
                    ->expectSingleCall(
                        fn($value) => $value,
                        Test::class,
                        result: fn(Subject $subject) => new Result($subject, $forwardedValue, [])
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $forwardedValue,
                        Result::class,
                        result: fn(Subject $subject) => new Result($subject, $expectedResult, [])
                    )
                    ->willBeCalledIf(fn($_1, $_2, array $successes) => $successes[0])
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) use ($expectedResult) {
                    if ($expectedResult !== $result->getValue()) {
                        return ['value does not match'];
                    }

                    return [];
                }
            );

        $builder->run();
    }
}
