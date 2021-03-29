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
use Philiagus\Parser\Parser\AssertStringMultibyte;
use Philiagus\Parser\Path\MetaInformation;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AssertStringMultibyteTest extends TestCase
{


    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertStringMultibyte()) instanceof Parser);
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
        self::assertSame($value, (new AssertStringMultibyte())->parse($value));
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
        (new AssertStringMultibyte())->parse($value);
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
        (new AssertStringMultibyte())->setTypeExceptionMessage($msg)->parse(false);
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
        (new AssertStringMultibyte())->withLength($lengthParser)->parse('012345678');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithLengthMultibyte(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse(1, Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $lengthParser */
        $lengthParser = $parser->reveal();
        (new AssertStringMultibyte())->withLength($lengthParser)->parse('ö');
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
        (new AssertStringMultibyte())->withSubstring(1, 3, $substringParser)->parse('abcdefg');
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
        (new AssertStringMultibyte())->withSubstring(100, 1000, $substringParser)->parse('abcdefg');
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
        (new AssertStringMultibyte())->withSubstring(0, 1, $substringParser)->parse('');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testMultibyteSubstring(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('ä', Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $substringParser */
        $substringParser = $parser->reveal();
        (new AssertStringMultibyte())->withSubstring(1, 1, $substringParser)->parse('üäö');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testSetEncoding(): void
    {
        $value = 'This is a UTF-8 string äöü';
        self::assertSame($value, (new AssertStringMultibyte())->setEncoding('UTF-8')->parse($value));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testSetEncodingException(): void
    {
        $value = utf8_decode('This is a UTF-8 string äöü decoded to ISO-8859-1');
        $this->expectException(ParsingException::class);
        self::assertSame($value, (new AssertStringMultibyte())->setEncoding('UTF-8')->parse($value));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testSetEncodingExceptionMessage(): void
    {
        $message = 'msg';
        $value = utf8_decode('This is a UTF-8 string äöü decoded to ISO-8859-1');
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($message);
        self::assertSame($value, (new AssertStringMultibyte())->setEncoding('UTF-8', $message)->parse($value));
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testSetEncodingOnlyAcceptsValidEncoding(): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertStringMultibyte())->setEncoding('this is not an encoding');
    }

    public function testThatEncodingCanOverwritten(): void
    {
        $parser = AssertStringMultibyte::new()
            ->setEncoding('UTF-8')
            ->setEncoding('ISO-8859-1');
        $value = utf8_decode('This is a UTF-8 string äöü decoded to ISO-8859-1');
        self::assertSame($value, $parser->parse($value));
    }

    public function testAllSetTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '5 integer integer 5'
        );
        (new AssertStringMultibyte())
            ->setTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse(5);
    }

    public function testAllSetEncodingExceptionMessageReplacers(): void
    {
        $char = mb_convert_encoding('Ü', 'ISO-8859-1', 'UTF-8');
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            $char . ' string string<binary>(1) | UTF-8 string string<ASCII>(5)"UTF-8"'
        );
        (new AssertStringMultibyte())
            ->setEncoding('UTF-8', '{value} {value.type} {value.debug} | {encoding} {encoding.type} {encoding.debug}')
            ->parse($char);
    }

    public function testEncodingNotDetectedException(): void
    {
        $string = "\xFF\xFE\xFD";
        $parser = new AssertStringMultibyte();
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('The encoding of the multibyte string could not be determined');
        $parser->parse($string);
    }

    public function testEncodingNotDetectedExceptionMessageOverwrite(): void
    {
        $string = "\xFF\xFE\xFD";
        $parser = (new AssertStringMultibyte())
            ->setEncodingDetectionExceptionMessage('overwrite {value} {value.type} {value.debug}');
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage("overwrite \xFF\xFE\xFD string string<binary>(3)");
        $parser->parse($string);
    }

    public function testWithRegex(): void
    {
        $parser = new AssertStringMultibyte();

        self::assertSame('v', $parser
            ->withRegex('/./')
            ->parse('v')
        );
    }

    public function testWithRegexInvalidRegexException(): void
    {
        $parser = new AssertStringMultibyte();
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('An invalid regular expression was provided');
        $parser->withRegex('');
    }

    public function testWithRegexNoMatchException(): void
    {
        $parser = AssertStringMultibyte::new()->withRegex('/a/');
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('The string does not match the expected pattern');
        $parser->parse('b');
    }

    public function testWithRegexNoMatchExceptionReplacers(): void
    {
        $parser = AssertStringMultibyte::new()->withRegex('/a/', '{value} {value.type} {value.debug} | {pattern} {pattern.type}');
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('b string string<ASCII>(1)"b" | /a/ string');
        $parser->parse('b');
    }



    public function testWithStartsWith(): void
    {
        self::assertSame('yes', AssertStringMultibyte::new()->withStartsWith('ye')->parse('yes'));
    }

    public function testWithStartsWithException(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('The string does not start with string<ASCII>(2)"ye"');
        AssertStringMultibyte::new()->withStartsWith('ye')->parse('nope');
    }

    public function testWithStartsWithExceptionReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('nopeye');
        AssertStringMultibyte::new()->withStartsWith('ye', '{value.raw}{expected.raw}')->parse('nope');
    }

    public function testWithStartsWithWorksBinary(): void
    {
        self::assertSame(
            'ü',
            AssertStringMultibyte::new()
                ->withStartsWith(substr('ü', 0, 1))
                ->parse('ü')
        );
    }

    public function testWithEndsWith(): void
    {
        self::assertSame('yes', AssertStringMultibyte::new()->withEndsWith('es')->parse('yes'));
    }

    public function testWithEndsWithException(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('The string does not end with string<ASCII>(2)"es"');
        AssertStringMultibyte::new()->withEndsWith('es')->parse('nope');
    }

    public function testWithEndsWithExceptionReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('nopees');
        AssertStringMultibyte::new()->withEndsWith('es', '{value.raw}{expected.raw}')->parse('nope');
    }

    public function testWithEndsWithWorksBinary(): void
    {
        self::assertSame(
            'ü',
            AssertStringMultibyte::new()
                ->withEndsWith(substr('ü', -1))
                ->parse('ü')
        );
    }

}