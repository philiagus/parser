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
use Philiagus\Parser\Parser\ConvertToArray;
use PHPUnit\Framework\TestCase;

class ConvertToArrayTest extends TestCase
{


    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new ConvertToArray()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return (new DataProvider(~DataProvider::TYPE_ARRAY))->provide();
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testParserConfigurationExceptionOnNoDefinedType(): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new ConvertToArray())->parse([]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return (new DataProvider(DataProvider::TYPE_ARRAY))->provide();
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidValues
     */
    public function testThatItAllowsArrayValues($value): void
    {
        $result = ConvertToArray::usingCast()
            ->parse($value);
        self::assertSame($value, $result);
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideInvalidValues
     */
    public function testThatItConvertsValuesToArrays($value): void
    {
        $result = (new ConvertToArray())
            ->setConvertToUseCast()
            ->parse($value);
        self::assertIsArray($result);
        self::assertCount(count((array) $value), $result);
        if (count($result)) {
            if (is_object($value)) {
                self::assertSame((array) $value, $result);
            } else {
                self::assertTrue(DataProvider::isSame($value, $result[0]));
            }
        }
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideInvalidValues
     */
    public function testThatItConvertsValuesWithDedicatedStringKey($value): void
    {
        $result = ConvertToArray::creatingArrayWithKey('key')
            ->parse($value);
        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertTrue(array_key_exists('key', $result));
        self::assertTrue(DataProvider::isSame($value, $result['key']));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidKeys(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER))->provide();
    }

    /**
     * @param $key
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidKeys
     */
    public function testThatConvertNonArraysAcceptsStringsAndIntegers($key): void
    {
        self::assertSame(
            [$key => 'value'],
            (new ConvertToArray())
                ->setConvertToCreateArrayWithKey($key)
                ->parse('value')
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidKeys(): array
    {
        return (new DataProvider(~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_STRING)))->provide();
    }

    /**
     * @param $wrongKey
     *
     * @dataProvider provideInvalidKeys
     *
     * @throws ParserConfigurationException
     */
    public function testThatConvertNonArraysBlocksNonStringInteger($wrongKey): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new ConvertToArray())
            ->setConvertToCreateArrayWithKey($wrongKey);
    }

}