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

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertString;
use Philiagus\Parser\Path\MetaInformation;
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
        return (new DataProvider(DataProvider::TYPE_STRING))->provide();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))->provide();
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
        (new AssertString())->setTypeExceptionMessage($msg)->parse(false);
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

    public function testAllSetTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '5 integer integer 5'
        );
        (new AssertString())
            ->setTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse(5);
    }

    public function testWithRegex(): void
    {
        $parser = new AssertString();

        self::assertSame('v', $parser
            ->withRegex('/./')
            ->parse('v')
        );
    }

    public function testWithRegexInvalidRegexException(): void
    {
        $parser = new AssertString();
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('An invalid regular expression was provided');
        $parser->withRegex('');
    }

    public function testWithRegexNoMatchException(): void
    {
        $parser = AssertString::new()->withRegex('/a/');
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('The string does not match the expected pattern');
        $parser->parse('b');
    }

    public function testWithRegexNoMatchExceptionReplacers(): void
    {
        $parser = AssertString::new()->withRegex('/a/', '{value} {value.type} {value.debug} | {pattern} {pattern.type}');
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('b string string<ASCII>(1)"b" | /a/ string');
        $parser->parse('b');
    }

    public function testWithStartsWith(): void
    {
        self::assertSame('yes', AssertString::new()->withStartsWith('ye')->parse('yes'));
    }

    public function testWithStartsWithException(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('The string does not start with string<ASCII>(2)"ye"');
        AssertString::new()->withStartsWith('ye')->parse('nope');
    }

    public function testWithStartsWithExceptionReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('nopeye');
        AssertString::new()->withStartsWith('ye', '{value.raw}{expected.raw}')->parse('nope');
    }

    public function testWithEndsWith(): void
    {
        self::assertSame('yes', AssertString::new()->withEndsWith('es')->parse('yes'));
    }

    public function testWithEndsWithException(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('The string does not end with string<ASCII>(2)"es"');
        AssertString::new()->withEndsWith('es')->parse('nope');
    }

    public function testWithEndsWithExceptionReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('nopees');
        AssertString::new()->withEndsWith('es', '{value.raw}{expected.raw}')->parse('nope');
    }
}