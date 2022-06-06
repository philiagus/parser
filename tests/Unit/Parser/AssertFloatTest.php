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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertFloat;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertFloat
 */
class AssertFloatTest extends TestCase
{

    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, ChainableParserTest, SetTypeExceptionMessageTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_FLOAT))
            ->map(fn($value) => [$value, fn() => AssertFloat::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_FLOAT))
            ->map(fn($value) => [$value, fn() => AssertFloat::new(), $value])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_FLOAT))
            ->map(fn($value) => [$value, fn() => AssertFloat::new()])
            ->provide(false);
    }

    public function testAssertMinimum(): void
    {
        $parser = AssertFloat::new()->assertMinimum(1.2);
        self::assertSame(1.3, $parser->parse(1.3));
        self::expectException(ParsingException::class);
        $parser->parse(1.1);
    }

    public function testAssertMaximum(): void
    {
        $parser = AssertFloat::new()->assertMaximum(1.2);
        self::assertSame(1.1, $parser->parse(1.1));
        self::expectException(ParsingException::class);
        $parser->parse(1.3);
    }

    public function provideInvalidFloats(): array
    {
        return (new DataProvider(DataProvider::TYPE_NAN | DataProvider::TYPE_INFINITE))
            ->provide();
    }

    /**
     * @param $value
     *
     * @return void
     * @throws ParserConfigurationException
     * @dataProvider provideInvalidFloats
     */
    public function testAssertMinimumInvalidArgument($value): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertFloat::new()->assertMinimum($value);
    }

    /**
     * @param $value
     *
     * @return void
     * @throws ParserConfigurationException
     * @dataProvider provideInvalidFloats
     */
    public function testAssertMaximumInvalidArgument($value): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertFloat::new()->assertMaximum($value);
    }

}
