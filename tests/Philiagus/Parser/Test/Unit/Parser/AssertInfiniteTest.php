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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertInfinite;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertInfinite
 */
class AssertInfiniteTest extends TestCase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        $parser = AssertInfinite::new();

        return (new DataProvider(DataProvider::TYPE_INFINITE))
            ->map(fn($value) => [$value, $parser, $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        $parser = AssertInfinite::new();

        return (new DataProvider(~DataProvider::TYPE_INFINITE))
            ->map(fn($value) => [$value, $parser])
            ->provide(false);
    }

    public function testAssertPositiveSuccess(): void
    {
        self::assertInfinite(
            AssertInfinite::new()
                ->setAssertSignToPositive()
                ->parse(INF)
        );
    }

    public function testAssertNegativeSuccess(): void
    {
        self::assertInfinite(
            AssertInfinite::new()
                ->setAssertSignToNegative()
                ->parse(-INF)
        );
    }

    public function testAssertPositiveException(): void
    {
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('Value: -INF');
        AssertInfinite::new()
            ->setAssertSignToPositive('Value: {value.raw}')
            ->parse(-INF);
    }

    public function testAssertNegativeException(): void
    {
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('Value: INF');
        AssertInfinite::new()
            ->setAssertSignToNegative('Value: {value.raw}')
            ->parse(INF);
    }
}
