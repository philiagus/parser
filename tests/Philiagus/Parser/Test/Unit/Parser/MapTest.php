<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\MultipleParsingException;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Map;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class MapTest extends TestCase
{
    use ProphecyTrait;

    public function testItExtendsBaseParser(): void
    {
        self::assertTrue(new Map() instanceof Parser);
    }

    public function testSameMapping(): void
    {
        $map = Map::new()
            ->addSame(0, $this->prophesizeNotTriggered())
            ->addSame('', $this->prophesizeTriggered(''))
            ->addSame(false, $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeNotTriggered(), $this->prophesizeNotTriggered());
        self::assertSame('RESULT', $map->parse(''));
    }

    private function prophesizeNotTriggered(): ParserContract
    {
        $notTriggered = $this->prophesize(ParserContract::class);
        $notTriggered->parse(Argument::any())->shouldNotBeCalled();

        return $notTriggered->reveal();
    }

    private function prophesizeTriggered($value, $result = 'RESULT'): ParserContract
    {
        $triggered = $this->prophesize(ParserContract::class);
        $triggered
            ->parse($value, Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn($result);

        return $triggered->reveal();
    }

    public function testSameListMapping(): void
    {
        $map = Map::new()
            ->addSameList([0, 1, 2], $this->prophesizeNotTriggered())
            ->addSameList(['', 0, false], $this->prophesizeTriggered(''))
            ->addSameList([false, true, 'value'], $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeNotTriggered(), $this->prophesizeNotTriggered());
        self::assertSame('RESULT', $map->parse(''));
    }

    public function testEqualsMapping(): void
    {
        $map = Map::new()
            ->addEquals(true, $this->prophesizeNotTriggered())
            ->addEquals(0, $this->prophesizeTriggered(0.0))
            ->addEquals('', $this->prophesizeNotTriggered())
            ->addEquals(false, $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeNotTriggered(), $this->prophesizeNotTriggered());
        self::assertSame('RESULT', $map->parse(0.0));
    }

    public function testEqualsListMapping(): void
    {
        $map = Map::new()
            ->addEqualsList([true, 'yes', 'agreed'], $this->prophesizeNotTriggered())
            ->addEqualsList([0, false, 'nope'], $this->prophesizeTriggered(''))
            ->addEqualsList(['', 'something', NAN], $this->prophesizeNotTriggered())
            ->addEqualsList([false, 'wrong'], $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeNotTriggered(), $this->prophesizeNotTriggered());
        self::assertSame('RESULT', $map->parse(''));
    }

    public function testParserMapping(): void
    {
        $map = Map::new()
            ->addSame(true, $this->prophesizeNotTriggered())
            ->addEquals('', $this->prophesizeNotTriggered())
            ->addSameList([1.0, 1], $this->prophesizeNotTriggered())
            ->addEqualsList([1, 2, 'VALUE'], $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeTriggered('value', 'not the value'), $this->prophesizeTriggered('value', 'RESULT'));
        self::assertSame('RESULT', $map->parse('value'));
    }

    public function testParserPipeMapping(): void
    {
        $map = Map::new()
            ->addSame(true, $this->prophesizeNotTriggered())
            ->addEquals('', $this->prophesizeNotTriggered())
            ->addSameList([1.0, 1], $this->prophesizeNotTriggered())
            ->addEqualsList([1, 2, 'VALUE'], $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeTriggered('value', 'not the value'), $this->prophesizeTriggered('not the value', 'RESULT'), true);
        self::assertSame('RESULT', $map->parse('value'));
    }

    public function testDefaltFallback(): void
    {
        $map = Map::new()
            ->addSame(true, $this->prophesizeNotTriggered())
            ->addEquals('', $this->prophesizeNotTriggered())
            ->addSameList([1.0, 1], $this->prophesizeNotTriggered())
            ->addEqualsList([1, 2, 'VALUE'], $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeTriggeredException('value'), $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeTriggeredException('value'), $this->prophesizeNotTriggered(), true)
            ->setDefaultResult('DEFAULT');
        self::assertSame('DEFAULT', $map->parse('value'));
    }

    private function prophesizeTriggeredException($value, string $message = 'EXCEPTION'): ParserContract
    {
        $triggered = $this->prophesize(ParserContract::class);
        $triggered
            ->parse($value, Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->will(function (array $args) use ($message) {
                throw new ParsingException($args[0], $message, $args[1]);
            });

        return $triggered->reveal();
    }

    public function testExceptionOnDefaltOverwriteAttempt(): void
    {
        $map = Map::new()
            ->setDefaultResult(1);

        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('The default for OneOf was already set and cannot be overwritten');

        $map->setDefaultResult(1);
    }

    public function testExceptionOnNoMatchNoDefault(): void
    {
        $map = Map::new()
            ->addSame(true, $this->prophesizeNotTriggered())
            ->addEquals('', $this->prophesizeNotTriggered())
            ->addSameList([1.0, 1], $this->prophesizeNotTriggered())
            ->addEqualsList([1, 2, 'VALUE'], $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeTriggeredException('value', 'exception1'), $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeTriggeredException('value', 'exception2'), $this->prophesizeNotTriggered(), true);

        $this->expectException(MultipleParsingException::class);
        $this->expectExceptionMessage('Provided value does not match any of the expected formats or values');
        $map->parse('value');
    }

    public function testExceptionOnNoMatchNoDefaultOverwrite(): void
    {
        $map = Map::new()
            ->overwriteNonOfExceptionMessage('{value} {value.type} {value.debug}')
            ->addSame(true, $this->prophesizeNotTriggered())
            ->addEquals('', $this->prophesizeNotTriggered())
            ->addSameList([1.0, 1], $this->prophesizeNotTriggered())
            ->addEqualsList([1, 2, 'VALUE'], $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeTriggeredException('value', 'exception1'), $this->prophesizeNotTriggered())
            ->addParser($this->prophesizeTriggeredException('value', 'exception2'), $this->prophesizeNotTriggered(), true);

        $this->expectException(MultipleParsingException::class);
        $this->expectExceptionMessage('value string string<ASCII>(5)"value"');
        $map->parse('value');
    }

    public function provideAllTypes(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    /**
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideAllTypes
     */
    public function testThatItMapsAnyValue($value): void
    {
        $matcher = $this->prophesize(ParserContract::class);
        $matcher->parse(Argument::any(), Argument::type(Path::class))->shouldBeCalledOnce();

        $parser =  $this->prophesize(ParserContract::class);
        $parser->parse(Argument::any(), Argument::type(Path::class))->shouldBeCalledOnce()->willReturn($value);

        DataProvider::assertSame($value, Map::new()
            ->addParser($matcher->reveal(), $parser->reveal())
            ->parse($value)
        );
    }

}
