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
use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Subject\Chain;
use Prophecy\Argument;

trait ChainableParserTest
{

    use ValidValueParserTest;

    abstract public function expectException(string $exception): void;

    /**
     * @param $value
     * @param \Closure $parser
     * @param $expected
     *
     * @throws ParsingException
     * @throws \Philiagus\Parser\Exception\RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThen($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        self::assertTrue(method_exists($parser, 'then'), 'method ->then doesn\'t exist on parser');
        $expectedResult = new \stdClass();
        $thenParser = $this->prophesize(Parser::class);
        /** @noinspection PhpParamsInspection */
        $thenParser
            ->parse(
                Argument::that(function ($argument) use ($expected) {
                    return DataProvider::isSame($expected, $argument);
                })
            )
            ->shouldBeCalledOnce()
            ->willReturn($expectedResult);
        $thenParser = $thenParser->reveal();
        $result = $parser
            ->then($thenParser)
            ->parse($value);

        DataProvider::isSame($expectedResult, $result);
    }

    /**
     * @param $value
     * @param \Closure $parser
     * @param $expected
     *
     * @throws ParsingException
     * @throws \Philiagus\Parser\Exception\RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThenWithOverwrittenParserNotValue($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        /** @var OverwritableParserDescription&Chainable $parser */
        self::assertTrue(method_exists($parser, 'then'), 'method ->then doesn\'t exist on parser');
        self::assertTrue(method_exists($parser, 'setChainDescription'), 'method ->setChainDescription doesn\'t exist on parser');
        $parser->setChainDescription('some description', false);
        $expectedResult = new \stdClass();
        $thenParser = $this->prophesize(Parser::class);
        /** @noinspection PhpParamsInspection */
        $thenParser
            ->parse(
                Argument::that(function ($argument) use ($expected) {
                    return DataProvider::isSame($expected, $argument);
                })
            )
            ->shouldBeCalledOnce()
            ->willReturn($expectedResult);
        $thenParser = $thenParser->reveal();
        $result = $parser
            ->then($thenParser)
            ->parse($value);

        DataProvider::isSame($expectedResult, $result);
    }

    /**
     * @param $value
     * @param \Closure $parser
     * @param $expected
     *
     * @throws ParsingException
     * @throws \Philiagus\Parser\Exception\RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThenWithOverwrittenParserInValue($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        /** @var OverwritableParserDescription&Chainable $parser */
        self::assertTrue(method_exists($parser, 'then'), 'method ->then doesn\'t exist on parser');
        self::assertTrue(method_exists($parser, 'setChainDescription'), 'method ->setChainDescription doesn\'t exist on parser');
        $parser->setChainDescription('some description', true);
        $expectedResult = new \stdClass();
        $thenParser = $this->prophesize(Parser::class);
        /** @noinspection PhpParamsInspection */
        $thenParser
            ->parse(
                Argument::that(function ($argument) use ($expected) {
                    return DataProvider::isSame($expected, $argument);
                })
            )
            ->shouldBeCalledOnce()
            ->willReturn($expectedResult);
        $thenParser = $thenParser->reveal();
        $result = $parser
            ->then($thenParser)
            ->parse($value);

        DataProvider::isSame($expectedResult, $result);
    }

    abstract public static function assertTrue($condition, string $message = ''): void;


}
