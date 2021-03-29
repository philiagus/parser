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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ConvertFromJson;
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
        return (new DataProvider(~DataProvider::TYPE_STRING))->provide();
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
        (new ConvertFromJson())->setConversionExceptionMessage($msg)->parse('u');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithConversionExceptionMessageWithReplacement(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('msg Syntax error');
        (new ConvertFromJson())->setConversionExceptionMessage('msg {msg}')->parse('u');
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
        (new ConvertFromJson())->setTypeExceptionMessage($msg)->parse(false);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithTypeExceptionWithReplace(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('msg boolean');
        (new ConvertFromJson())->setTypeExceptionMessage('msg {value.gettype}')->parse(false);
    }

    public function testAllSetTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '5 integer integer 5'
        );
        (new ConvertFromJson())
            ->setTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse(5);
    }

    public function testAllSetConversionExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '. string string<ASCII>(1)"." | Syntax error string string<ASCII>(12)"Syntax error"'
        );
        (new ConvertFromJson())
            ->setConversionExceptionMessage('{value} {value.type} {value.debug} | {msg} {msg.type} {msg.debug}')
            ->parse('.');
    }


}
