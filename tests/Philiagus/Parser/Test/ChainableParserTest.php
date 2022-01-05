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

namespace Philiagus\Parser\Test;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Contract\ChainableParser;
use Philiagus\Parser\Contract\Parser;
use Prophecy\Argument;

trait ChainableParserTest {

    use ValidValueParserTest;
    abstract public function expectException(string $exception): void;
    abstract public static function assertTrue($condition, string $message = ''): void;

    /**
     * @param $value
     * @param ChainableParser $parser
     * @param $expected
     *
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @throws \Philiagus\Parser\Exception\ParsingException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testAssignTo($value, ChainableParser $parser, $expected): void
    {
        $result = $parser
            ->thenAssignTo($target)
            ->parse($value);

        self::assertTrue(DataProvider::isSame($expected, $target));
        self::assertTrue(DataProvider::isSame($expected, $result));
    }

    /**
     * @param $value
     * @param ChainableParser $parser
     * @param $expected
     *
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @throws \Philiagus\Parser\Exception\ParsingException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testAppendTo($value, ChainableParser $parser, $expected): void
    {
        $result = $parser
            ->thenAppendTo($target)
            ->parse($value);

        self::assertTrue(DataProvider::isSame($expected, $result));
        self::assertTrue(DataProvider::isSame([$expected], $target));
    }

    /**
     * @param $value
     * @param Parser $parser
     * @param $expected
     *
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @throws \Philiagus\Parser\Exception\ParsingException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThen($value, ChainableParser $parser, $expected): void
    {
        $expectedResult = new \stdClass();
        $thenParser = $this->prophesize(Parser::class);
        $thenParser
            ->parse(
                Argument::that(function ($argument) use ($expected) {
                    return DataProvider::isSame($expected, $argument);
                }),
                null
            )
            ->shouldBeCalledOnce()
            ->willReturn($expectedResult);
        $thenParser = $thenParser->reveal();
        $result = $parser
            ->then($thenParser)
            ->parse($value);

        DataProvider::isSame($expectedResult, $result);
    }



}
