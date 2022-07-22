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
use Philiagus\Parser\Parser\AssertFloat;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\AssertFloat
 */
class AssertFloatTest extends ParserTestBase
{

    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, ChainableParserTest, SetTypeExceptionMessageTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_FLOAT))
            ->map(static fn($value) => [$value, static fn() => AssertFloat::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_FLOAT))
            ->map(static fn($value) => [$value, static fn() => AssertFloat::new(), $value])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_FLOAT))
            ->map(static fn($value) => [$value, static fn() => AssertFloat::new()])
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
            ->successProvider(DataProvider::TYPE_FLOAT);
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
            ->successProvider(DataProvider::TYPE_FLOAT);
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
