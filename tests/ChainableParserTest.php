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

use DateTimeInterface;
use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Exception\RuntimeParserConfigurationException;
use Philiagus\Parser\Result;
use Prophecy\Argument;
use SplDoublyLinkedList;

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
     * @throws RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThenMultichain($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        /** @var Contract\Chainable $parser */
        self::assertInstanceOf(Contract\Chainable::class, $parser);
        $expectedResult = new \stdClass();
        $thenParser = $this->prophesize(Parser::class);
        /** @noinspection PhpParamsInspection */
        $thenParser
            ->parse(
                Argument::that(function (\Philiagus\Parser\Contract\Subject $subject) use ($expected) {
                    $value = $subject->getValue();
                    if ($value instanceof DateTimeInterface && $expected instanceof DateTimeInterface) {
                        return $value::class === $expected::class &&
                            $value->format('Y-m-d H:i:s.u') == $expected->format('Y-m-d H:i:s.u');
                    }

                    return DataProvider::isSame($expected, $value);
                })
            )
            ->shouldBeCalledOnce()
            ->will(function (array $args) use ($expectedResult) {
                return new Result($args[0], $expectedResult, []);
            });
        $thenParser = $thenParser->reveal();
        $appendTarget2 = new SplDoublyLinkedList();
        $appendTarget3 = [];
        /** @var \Philiagus\Parser\Contract\Result $result */
        $result = $parser
            ->then($thenParser)
            ->thenAssignTo($assignTarget)
            ->thenAppendTo($appendTarget)
            ->thenAppendTo($appendTarget2)
            ->thenAppendTo($appendTarget3)
            ->parse(Subject::default($value));

        Util::assertSame($expectedResult, $result->getValue());
        Util::assertSame($expectedResult, $assignTarget);
        Util::assertSame([$expectedResult], $appendTarget);
        Util::assertSame([$expectedResult], iterator_to_array($appendTarget2));
        Util::assertSame([$expectedResult], $appendTarget3);
    }

    /**
     * @param $value
     * @param \Closure $parser
     * @param $expected
     *
     * @throws ParsingException
     * @throws RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThen($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        /** @var Contract\Chainable $parser */
        self::assertInstanceOf(Contract\Chainable::class, $parser);
        $expectedResult = new \stdClass();
        $thenParser = $this->prophesize(Parser::class);
        /** @noinspection PhpParamsInspection */
        $thenParser
            ->parse(
                Argument::that(function (\Philiagus\Parser\Contract\Subject $subject) use ($expected) {
                    $value = $subject->getValue();
                    if ($value instanceof DateTimeInterface && $expected instanceof DateTimeInterface) {
                        return $value::class === $expected::class &&
                            $value->format('Y-m-d H:i:s.u') == $expected->format('Y-m-d H:i:s.u');
                    }

                    return DataProvider::isSame($expected, $value);
                })
            )
            ->shouldBeCalledOnce()
            ->will(function (array $args) use ($expectedResult) {
                return new Result($args[0], $expectedResult, []);
            });
        $thenParser = $thenParser->reveal();
        /** @var \Philiagus\Parser\Contract\Result $result */
        $result = $parser
            ->then($thenParser)
            ->parse(Subject::default($value));

        Util::assertSame($expectedResult, $result->getValue());
    }
    /**
     * @param $value
     * @param \Closure $parser
     * @param $expected
     *
     * @throws ParsingException
     * @throws RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThenAssignTo($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        /** @var Contract\Chainable $parser */
        self::assertInstanceOf(Contract\Chainable::class, $parser);
        $expectedResult = new \stdClass();
        /** @var \Philiagus\Parser\Contract\Result $result */
        $result = $parser
            ->thenAssignTo($assignTarget)
            ->parse(Subject::default($value));

        Util::assertSame($expected, $result->getValue());
        Util::assertSame($expected, $assignTarget);
    }
    /**
     * @param $value
     * @param \Closure $parser
     * @param $expected
     *
     * @throws ParsingException
     * @throws RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThenAppendTo_unsetVariable($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        /** @var Contract\Chainable $parser */
        self::assertInstanceOf(Contract\Chainable::class, $parser);
        /** @var \Philiagus\Parser\Contract\Result $result */
        $result = $parser
            ->thenAppendTo($appendTarget)
            ->parse(Subject::default($value));

        Util::assertSame($expected, $result->getValue());
        Util::assertSame([$expected], $appendTarget);
    }
    /**
     * @param $value
     * @param \Closure $parser
     * @param $expected
     *
     * @throws ParsingException
     * @throws RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThenAppendTo_arrayVariable($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        /** @var Contract\Chainable $parser */
        self::assertInstanceOf(Contract\Chainable::class, $parser);
        /** @var \Philiagus\Parser\Contract\Result $result */
        $appendTarget = [];
        $result = $parser
            ->thenAppendTo($appendTarget)
            ->parse(Subject::default($value));

        Util::assertSame($expected, $result->getValue());
        Util::assertSame([$expected], $appendTarget);
    }
    /**
     * @param $value
     * @param \Closure $parser
     * @param $expected
     *
     * @throws ParsingException
     * @throws RuntimeParserConfigurationException
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThenAppendTo_objectVariable($value, \Closure $parser, $expected): void
    {
        $parser = $parser($value);
        /** @var Contract\Chainable $parser */
        self::assertInstanceOf(Contract\Chainable::class, $parser);
        /** @var \Philiagus\Parser\Contract\Result $result */
        $appendTarget = new \SplDoublyLinkedList();
        $result = $parser
            ->thenAppendTo($appendTarget)
            ->parse(Subject::default($value));

        Util::assertSame($expected, $result->getValue());
        Util::assertSame([$expected], iterator_to_array($appendTarget));
    }

    abstract public static function assertTrue($condition, string $message = ''): void;

}
