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

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\AssertInteger
 */
class AssertIntegerTest extends ParserTestBase
{


    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, ChainableParserTestTrait;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INTEGER))
            ->map(static fn($value) => [$value, static fn() => AssertInteger::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INTEGER))
            ->map(static fn($value) => [$value, static fn() => AssertInteger::new(), $value])
            ->provide(false);
    }

    public function testAssertMinimum(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => PHP_INT_MIN, fn($value) => $value !== PHP_INT_MIN)
                    ->error(fn($value) => PHP_INT_MAX, fn($value) => $value !== PHP_INT_MAX),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0])
                    ->withParameterElement('min', 0)
            )
            ->successProvider(DataProvider::TYPE_INTEGER);
        $builder->run();
    }

    public function testAssertMaximum(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => PHP_INT_MAX, fn($value) => $value !== PHP_INT_MAX)
                    ->error(fn($value) => PHP_INT_MIN, fn($value) => $value !== PHP_INT_MIN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value > $args[0])
                    ->withParameterElement('max', 0)
            )
            ->successProvider(DataProvider::TYPE_INTEGER);
        $builder->run();
    }

    public function testAssertMultipleOf(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value, description: 'same value')
                    ->success(fn($value) => intdiv($value, 2), fn($value) => $value / 2 === intdiv($value, 2), description: 'half value')
                    ->success(fn($value) => 1, description: 'value 1')
                    ->error(fn($value) => 0, fn($value) => $value !== 0, description: 'zero'),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args, array $successes) => !$successes[0])
                    ->withParameterElement('base', 0)
            )
            ->successProvider(DataProvider::TYPE_INTEGER);
        $builder->run();
    }
}
