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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ParseJson;
use Philiagus\Parser\Type\AcceptsString;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class ParseJsonTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new ParseJson()) instanceof Parser);
    }

    public function testThatItAcceptsString()
    {
        self::assertTrue((new ParseJson()) instanceof AcceptsString);
    }

    public function provideNonStringValues(): array
    {
        return DataProvider::provide(~DataProvider::TYPE_STRING);
    }

    /**
     * @param $value
     *
     * @throws ParsingException
     * @throws \Philiagus\Parser\Exception\MultipleParsingException
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @dataProvider provideNonStringValues
     */
    public function testExceptionOnNonStringValues($value): void
    {
        self::expectException(ParsingException::class);
        (new ParseJson())->parse($value);
    }

    public function testExceptionOnNonJsonString(): void
    {
        self::expectException(ParsingException::class);
        (new ParseJson())->parse('not a json');
    }

    public function testMaxDepth(): void
    {
        $json = [
            'a'
        ];
        $jsonString = json_encode($json);
        self::assertSame($json, (new ParseJson())->withMaxDepth(10)->parse($jsonString));
    }

    public function testMaxDepthException(): void
    {
        self::expectException(ParsingException::class);
        (new ParseJson())->withMaxDepth(1)->parse('[[[[[[[[[[[[[1]]]]]]]]]]]]]');
    }

    public function testWithObjectsAsArrays(): void
    {
        $parser = (new ParseJson());
        self::assertInstanceOf(\stdClass::class, $parser->parse('{}'));
        $parser->withObjectsAsArrays();
        self::assertSame([], $parser->parse('{}'));
    }

    public function testBigintAsString(): void
    {
        $parser = (new ParseJson());
        $int = PHP_INT_MAX . '0';
        self::assertIsFloat($parser->parse($int));
        $parser->withBigintAsString();
        self::assertSame($int, $parser->parse($int));
    }

}
