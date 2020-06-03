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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertStringMultibyte;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Test\Provider\DataProvider;
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
        (new AssertStringMultibyte())->overwriteTypeExceptionMessage($msg)->parse(false);
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testWithLength(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute(9, Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var Parser $lengthParser */
        $lengthParser = $parser->reveal();
        (new AssertStringMultibyte())->withLength($lengthParser)->parse('012345678');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithLengthMultibyte(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute(1, Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var Parser $lengthParser */
        $lengthParser = $parser->reveal();
        (new AssertStringMultibyte())->withLength($lengthParser)->parse('ö');
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testWithSubstring(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('bcd', Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var Parser $substringParser */
        $substringParser = $parser->reveal();
        (new AssertStringMultibyte())->withSubstring(1, 3, $substringParser)->parse('abcdefg');
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testEmptySubstringOnOutOfBounds(): void
    {

        $parser = $this->prophesize(Parser::class);
        $parser->execute('', Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var Parser $substringParser */
        $substringParser = $parser->reveal();
        (new AssertStringMultibyte())->withSubstring(100, 1000, $substringParser)->parse('abcdefg');
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testEmptySubstringOnEmptyValue(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('', Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var Parser $substringParser */
        $substringParser = $parser->reveal();
        (new AssertStringMultibyte())->withSubstring(0, 1, $substringParser)->parse('');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testMultibyteSubstring(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('ä', Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var Parser $substringParser */
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

    /**
     * @throws ParserConfigurationException
     */
    public function testThatEncodingCannotBeOverwritten(): void
    {
        $parser = AssertStringMultibyte::new()->setEncoding('UTF-8');
        self::expectException(ParserConfigurationException::class);
        $parser->setEncoding('ISO-8859-1');
    }

}