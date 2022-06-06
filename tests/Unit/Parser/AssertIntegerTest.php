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
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertInteger
 */
class AssertIntegerTest extends TestCase
{


    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, ChainableParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INTEGER))
            ->map(fn($value) => [$value, fn() => AssertInteger::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INTEGER))
            ->map(fn($value) => [$value, fn() => AssertInteger::new(), $value])
            ->provide(false);
    }

    public function testAssertMinimum(): void
    {
        $parser = AssertInteger::new()->assertMinimum(1);
        $parser->parse(2);
        $parser->parse(1);
        self::expectException(ParsingException::class);
        $parser->parse(0);
    }

    public function testAssertMaximum(): void
    {
        $parser = AssertInteger::new()->assertMaximum(1);
        $parser->parse(0);
        $parser->parse(1);
        self::expectException(ParsingException::class);
        $parser->parse(2);
    }

    public function testAssertMultipleOf(): void
    {
        $parser = AssertInteger::new()->assertMultipleOf(2);
        $parser->parse(0);
        $parser->parse(2);
        $parser->parse(8);
        self::expectException(ParsingException::class);
        $parser->parse(3);
    }

    public function testAssertMultipleOfZero(): void
    {
        $parser = AssertInteger::new()->assertMultipleOf(0);
        $parser->parse(0);
        self::expectException(ParsingException::class);
        $parser->parse(1);
    }
}
