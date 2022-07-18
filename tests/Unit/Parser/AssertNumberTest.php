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
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertNumber
 */
class AssertNumberTest extends ParserTestBase
{


    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, ChainableParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT)))
            ->map(static fn($value) => [$value, static fn() => AssertNumber::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT))
            ->map(static fn($value) => [$value, static fn() => AssertNumber::new(), $value])
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
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0])
                    ->withParameterElement('min', 0)
            )
            ->successProvider(DataProvider::TYPE_FLOAT|DataProvider::TYPE_INTEGER);
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
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value > $args[0])
                    ->withParameterElement('max', 0)
            )
            ->successProvider(DataProvider::TYPE_FLOAT|DataProvider::TYPE_INTEGER);
        $builder->run();
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
