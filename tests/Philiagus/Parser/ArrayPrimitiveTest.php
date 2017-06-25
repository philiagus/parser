<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Test\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\ArrayPrimitive;
use PHPUnit\Framework\TestCase;

class ArrayPrimitiveTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new ArrayPrimitive()) instanceof Parser);
    }

    public function provideInvalidValues(): array
    {
        return DataProvider::provide(~DataProvider::TYPE_ARRAY);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatItBlocksNonBooleanValues($value)
    {
        (new ArrayPrimitive())->parse($value);
    }

    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ARRAY);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideValidValues
     */
    public function testThatItAllowsBooleanValues($value)
    {
        $result = (new ArrayPrimitive())->parse($value);
        self::assertSame($value, $result);
    }


    /**
     * @expectedException \Philiagus\Parser\Exception\ParserConfigurationException
     */
    public function testThatItOnlyAllowsPositiveMinimumCount()
    {
        (new ArrayPrimitive())->withMinimumCount(-1);
    }

    public function provideMinimumCountAndArrays(): array
    {
        return [
            [1, ['one element']],
            [5, [1, 2, 3, 4, 5, 6, 7, 8, 9, 0]],
        ];
    }

    /**
     * @param int $minimum
     * @param array $array
     *
     * @dataProvider provideMinimumCountAndArrays
     */
    public function testThatItRespectsMinimumCount(int $minimum, array $array)
    {
        $result = (new ArrayPrimitive())
            ->withMinimumCount($minimum)
            ->parse($array);
        self::assertSame($array, $result);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatItBlocksMinimumUnderflow()
    {
        (new ArrayPrimitive())
            ->withMinimumCount(1)
            ->parse([]);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParserConfigurationException
     */
    public function testThatItOnlyAllowsPositiveMaximumCount()
    {
        (new ArrayPrimitive())->withMinimumCount(-1);
    }

    public function provideMaximumCountAndArrays(): array
    {
        return [
            [1, ['one element']],
            [5, [1, 2]],
        ];
    }

    /**
     * @param int $maximum
     * @param array $array
     *
     * @dataProvider provideMaximumCountAndArrays
     */
    public function testThatItRespectsMaximumCount(int $maximum, array $array)
    {
        $result = (new ArrayPrimitive())
            ->withMaximumCount($maximum)
            ->parse($array);
        self::assertSame($array, $result);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatItBlocksMaximumOverflow()
    {
        (new ArrayPrimitive())
            ->withMaximumCount(0)
            ->parse([1]);
    }

}