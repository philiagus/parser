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

namespace Philiagus\Test\Parser\Unit\Parser;

use Exception;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ConvertFromJson;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ConvertFromJsonTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new ConvertFromJson()) instanceof Parser);
    }

    /**
     * @return array
     * @throws Exception
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
        self::assertSame($json, (new ConvertFromJson())->withMaxDepth(10)->parse($jsonString));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testMaxDepthException(): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertFromJson())->withMaxDepth(1)->parse('[[[[[[[[[[[[[1]]]]]]]]]]]]]');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithObjectsAsArrays(): void
    {
        $parser = (new ConvertFromJson());
        self::assertInstanceOf(stdClass::class, $parser->parse('{}'));
        $parser->withObjectsAsArrays();
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
        $parser->withBigintAsString();
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
        (new ConvertFromJson())->withConversionExceptionMessage($msg)->parse('u');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithConversionExceptionMessageWithReplacement(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('msg Syntax error');
        (new ConvertFromJson())->withConversionExceptionMessage('msg {msg}')->parse('u');
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
        (new ConvertFromJson())->withTypeExceptionMessage($msg)->parse(false);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithTypeExceptionWithReplace(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('msg boolean');
        (new ConvertFromJson())->withTypeExceptionMessage('msg {type}')->parse(false);
    }

}
