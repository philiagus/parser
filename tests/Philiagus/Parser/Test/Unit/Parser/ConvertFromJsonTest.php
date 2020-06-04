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
use Philiagus\Parser\Parser\ConvertFromJson;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class ConvertFromJsonTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new ConvertFromJson()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideNonStringValues(): array
    {
        return DataProvider::provide((int) ~DataProvider::TYPE_STRING);
    }

    /**
     * @param $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideNonStringValues
     */
    public function testExceptionOnNonStringValues($value): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertFromJson())->parse($value);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testExceptionOnNonJsonString(): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertFromJson())->parse('not a json');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testMaxDepth(): void
    {
        $json = [
            'a',
        ];
        $jsonString = json_encode($json);
        self::assertSame($json, (new ConvertFromJson())->setMaxDepth(10)->parse($jsonString));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testMaxDepthException(): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertFromJson())->setMaxDepth(1)->parse('[[[[[[[[[[[[[1]]]]]]]]]]]]]');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithObjectsAsArrays(): void
    {
        $parser = (new ConvertFromJson());
        self::assertInstanceOf(\stdClass::class, $parser->parse('{}'));
        $parser->setObjectsAsArrays();
        self::assertSame([], $parser->parse('{}'));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testBigintAsString(): void
    {
        $parser = (new ConvertFromJson());
        $int = PHP_INT_MAX . '0';
        self::assertIsFloat($parser->parse($int));
        $parser->setBigintAsString();
        self::assertSame($int, $parser->parse($int));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithConversionExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new ConvertFromJson())->overwriteConversionExceptionMessage($msg)->parse('u');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithConversionExceptionMessageWithReplacement(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('msg Syntax error');
        (new ConvertFromJson())->overwriteConversionExceptionMessage('msg {msg}')->parse('u');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithTypeException(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new ConvertFromJson())->overwriteTypeExceptionMessage($msg)->parse(false);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithTypeExceptionWithReplace(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('msg boolean');
        (new ConvertFromJson())->overwriteTypeExceptionMessage('msg {value.gettype}')->parse(false);
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatObjectsAsArrayCannotBeOverwritten(): void
    {
        $parser = ConvertFromJson::new()->setObjectsAsArrays();
        $this->expectException(ParserConfigurationException::class);
        $parser->setObjectsAsArrays(false);
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatMaxDepthCannotBeOverwritten(): void
    {
        $parser = ConvertFromJson::new()->setMaxDepth(100);
        $this->expectException(ParserConfigurationException::class);
        $parser->setMaxDepth(500);
    }


    /**
     * @throws ParserConfigurationException
     */
    public function testThatBigintAsStringCannotBeOverwritten(): void
    {
        $parser = ConvertFromJson::new()->setBigintAsString();
        $this->expectException(ParserConfigurationException::class);
        $parser->setBigintAsString(false);
    }

    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '5 integer integer 5'
        );
        (new ConvertFromJson())
            ->overwriteTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse(5);
    }

    public function testAllOverwriteConversionExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '. string string<ASCII>(1)"." | Syntax error string string<ASCII>(12)"Syntax error"'
        );
        (new ConvertFromJson())
            ->overwriteConversionExceptionMessage('{value} {value.type} {value.debug} | {msg} {msg.type} {msg.debug}')
            ->parse('.');
    }


}
