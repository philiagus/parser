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
use Philiagus\Parser\Parser\AssertNumber;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertNumber
 */
class AssertNumberTest extends TestCase
{


    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, ChainableParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT)))
            ->map(fn($value) => [$value, fn() => AssertNumber::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT))
            ->map(fn($value) => [$value, fn() => AssertNumber::new(), $value])
            ->provide(false);
    }

    public function testAssertMinimum(): void
    {
        $parser = AssertNumber::new()->assertMinimum(5.1);
        $parser->parse(6);
        $parser->parse(5.1);
        self::expectException(ParsingException::class);
        $parser->parse(5.09);
    }

    public function testAssertMaximum(): void
    {
        $parser = AssertNumber::new()->assertMaximum(5.1);
        $parser->parse(5);
        $parser->parse(5.1);
        self::expectException(ParsingException::class);
        $parser->parse(5.10001);
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
        AssertNumber::new()->assertMinimum($value);
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
        AssertNumber::new()->assertMaximum($value);
    }


}
