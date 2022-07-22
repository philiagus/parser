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

namespace Philiagus\Parser\Test\Unit\Parser;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\ConvertToDateTime;
use Philiagus\Parser\Result;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ParserTestBase;

/**
 * @covers \Philiagus\Parser\Parser\ConvertToDateTime
 */
class ConvertToDateTimeTest extends ParserTestBase
{

    use ChainableParserTest, InvalidValueParserTest;


    public function provideValidValuesAndParsersAndResults(): array
    {
        return [
            'unix timestamp conversion' => [
                0,
                fn() => ConvertToDateTime::fromSourceFormat('!U'),
                DateTime::createFromFormat('!U', '0'),
            ],
            'string conversion' => [
                '2001-01-01',
                fn() => ConvertToDateTime::fromSourceFormat('!Y-m-d'),
                DateTime::createFromFormat('!Y-m-d', '2001-01-01'),
            ],
            'no conversion' => [
                $value = new \DateTime(),
                fn() => ConvertToDateTime::new(),
                $value,
            ],
            'no conversion immutable' => [
                $value = new \DateTimeImmutable(),
                fn() => ConvertToDateTime::new()->setImmutable(),
                $value,
            ],
        ];
    }

    public function testFromSourceFormat(): void
    {
        $cases = [
            [123, '!U'],
            ['2001-02-03', '!Y-m-d'],
            [new \DateTimeImmutable(), 'irrelevant'],
            [new \DateTime(), 'irrelevant'],
        ];
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        function ($value) use ($cases) {
                            foreach ($cases as [$sourceValue, $format]) {
                                if ($sourceValue === $value) return $format;
                            }

                            return 'INVALID';
                        }
                    )
                    ->error(
                        fn() => 'NOT A FORMAT',
                        fn($value) => !$value instanceof DateTimeInterface
                    ),
                $builder
                    ->fixedArgument()
                    ->success(new DateTimeZone('Europe/Berlin'), 'Europe/Berlin')
                    ->success(new DateTimeZone('UTC'), 'UTC')
                    ->success(null, 'no timezone'),
                $builder
                    ->messageArgument()
                    ->withParameterElement('format', 0)
                    ->expectedWhen(fn($_1, $_2, array $successes) => !$successes[0])
            )
            ->values(
                array_column($cases, 0),
                successValidator: function (Subject $subject, Result $result, array $arguments) use ($cases) {
                    $relevantFormat = null;
                    $value = $subject->getValue();
                    $resultValue = $result->getValue();
                    if (!$resultValue instanceof DateTimeInterface) {
                        return ['Result is not a DateTimeInterface'];
                    }
                    foreach ($cases as [$sourceValue, $format]) {
                        if ($sourceValue === $value) {
                            $relevantFormat = $format;
                            break;
                        }
                    }
                    if (!$relevantFormat) return ['Source format could not be evaluated'];
                    $expectedValue = $value;
                    if (!$value instanceof DateTimeInterface) {
                        $expectedValue = DateTime::createFromFormat($relevantFormat, (string) $expectedValue, $arguments[1]);
                    } else {
                        $expectedValue = DateTime::createFromInterface($value);
                    }
                    $a = $expectedValue::class . '[' . $expectedValue->format('r') . ']';
                    $b = $resultValue::class . '[' . $resultValue->format('r') . ']';
                    if ($a !== $b) {
                        return ['Result values do not match: ' . $a . ' <=> ' . $b];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function testSetStringSourceFormat(): void
    {
        $cases = [
            [123, '!U'],
            ['2001-02-03', '!Y-m-d'],
            [new \DateTimeImmutable(), 'irrelevant'],
            [new \DateTime(), 'irrelevant'],
        ];
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        function ($value) use ($cases) {
                            foreach ($cases as [$sourceValue, $format]) {
                                if ($sourceValue === $value) return $format;
                            }

                            return 'INVALID';
                        }
                    )
                    ->error(
                        fn() => 'NOT A FORMAT',
                        fn($value) => !$value instanceof DateTimeInterface
                    ),
                $builder
                    ->fixedArgument()
                    ->success(new DateTimeZone('Europe/Berlin'), 'Europe/Berlin')
                    ->success(new DateTimeZone('UTC'), 'UTC')
                    ->success(null, 'no timezone'),
                $builder
                    ->messageArgument()
                    ->withParameterElement('format', 0)
                    ->expectedWhen(fn($_1, $_2, array $successes) => !$successes[0])
            )
            ->call(
                'setImmutable',
                $builder
                    ->fixedArgument()
                    ->success(false, 'not immutable')
                    ->success(true, 'immutable')
            )
            ->values(
                array_column($cases, 0),
                successValidator: function (Subject $subject, Result $result, array $arguments) use ($cases) {
                    $relevantFormat = null;
                    $value = $subject->getValue();
                    $resultValue = $result->getValue();
                    if (!$resultValue instanceof DateTimeInterface) {
                        return ['Result is not a DateTimeInterface'];
                    }
                    foreach ($cases as [$sourceValue, $format]) {
                        if ($sourceValue === $value) {
                            $relevantFormat = $format;
                            break;
                        }
                    }
                    if (!$relevantFormat) return ['Source format could not be evaluated'];
                    $expectedValue = $value;
                    if (!$value instanceof DateTimeInterface) {
                        $expectedValue = DateTime::createFromFormat($relevantFormat, (string) $expectedValue, $arguments[1]);
                    } else {
                        $expectedValue = DateTime::createFromInterface($value);
                    }
                    if($arguments[3]) {
                        $expectedValue = DateTimeImmutable::createFromInterface($expectedValue);
                    }
                    $a = $expectedValue::class . '[' . $expectedValue->format('r') . ']';
                    $b = $resultValue::class . '[' . $resultValue->format('r') . ']';
                    if ($a !== $b) {
                        return ['Result values do not match: ' . $a . ' <=> ' . $b];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function testSetTimezone(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->success(new DateTimeZone('Europe/Berlin'), 'Europe/Berlin')
                    ->success(new DateTimeZone('UTC'), 'UTC')
            )
            ->values(
                [
                    new DateTimeImmutable(),
                    new DateTime(),
                ],
                successValidator: function (Subject $subject, Result $result, array $arguments) {
                    $value = $subject->getValue();
                    $resultValue = $result->getValue();
                    if (!$resultValue instanceof DateTimeInterface) {
                        return ['Result is not a DateTimeInterface'];
                    }
                    $expectedValue = DateTime::createFromInterface($value)->setTimezone($arguments[0]);
                    $a = $expectedValue::class . '[' . $expectedValue->format('r') . ']';
                    $b = $resultValue::class . '[' . $resultValue->format('r') . ']';
                    if ($a !== $b) {
                        return ['Result values do not match: ' . $a . ' <=> ' . $b];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function provideInvalidValuesAndParsers(): array
    {

        return (new DataProvider())
            ->filter(
                fn($value) => !$value instanceof DateTimeInterface
            )
            ->map(
                fn($value) => [$value, fn() => ConvertToDateTime::new()]
            )
            ->provide(false);
    }
}
