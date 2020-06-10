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
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertString;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AssertStringTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertString()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_STRING);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int) ~DataProvider::TYPE_STRING);
    }

    /**
     * @param $value
     *
     * @dataProvider provideValidValues
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testThatItAcceptsString($value): void
    {
        self::assertSame($value, (new AssertString())->parse($value));
    }

    /**
     * @param $value
     *
     * @dataProvider  provideInvalidValues
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testThatItBlocksNonString($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertString())->parse($value);
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testWithTypeExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new AssertString())->overwriteTypeExceptionMessage($msg)->parse(false);
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testWithLength(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse(9, Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $lengthParser */
        $lengthParser = $parser->reveal();
        (new AssertString())->withLength($lengthParser)->parse('012345678');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithLengthMultibyte(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse(2, Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $lengthParser */
        $lengthParser = $parser->reveal();
        (new AssertString())->withLength($lengthParser)->parse('ö');
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testWithSubstring(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('bcd', Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $substringParser */
        $substringParser = $parser->reveal();
        (new AssertString())->withSubstring(1, 3, $substringParser)->parse('abcdefg');
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testEmptySubstringOnOutOfBounds(): void
    {

        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('', Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $substringParser */
        $substringParser = $parser->reveal();
        (new AssertString())->withSubstring(100, 1000, $substringParser)->parse('abcdefg');
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testEmptySubstringOnEmptyValue(): void
    {

        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('', Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $substringParser */
        $substringParser = $parser->reveal();
        (new AssertString())->withSubstring(0, 1, $substringParser)->parse('');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testSubstringMultibyte(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        // ö consists of the bytes c3 96
        $parser->parse(substr('ö', 1, 1), Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $substringParser */
        $substringParser = $parser->reveal();
        (new AssertString())->withSubstring(1, 1, $substringParser)->parse('öäü');
    }

    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '5 integer integer 5'
        );
        (new AssertString())
            ->overwriteTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse(5);
    }

}