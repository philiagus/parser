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
use Philiagus\Parser\IntegerConverter;
use PHPUnit\Framework\TestCase;

class IntegerConverterTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new IntegerConverter()) instanceof Parser);
    }

    public function provideIncompatibleValues()
    {
        return
            array_merge(
                DataProvider::provide(~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT | DataProvider::TYPE_STRING)),
                [
                    'string non numeric' => ['asdf'],
                    'string almost numeric' => ['0asdf'],
                    'float overflow' => [PHP_INT_MAX + .5],
                    'float underflow' => [PHP_INT_MIN - .5],
                ]
            );
    }

    /**
     * @param $incompatibleValue
     *
     * @dataProvider provideIncompatibleValues
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatItDoesNotAcceptIncompatibleValues($incompatibleValue)
    {
        (new IntegerConverter())->parse($incompatibleValue);
    }

    public function compatibleValueProvider()
    {
        $cases =
            [
                ['1', 1],
                ['-100', -100],
                [1.0, 1],
                [-1.0, -1],
                [-0, 0],
                ['-0', 0],
                ['0', 0],
            ];

        $data = [];
        foreach ($cases as [$from, $to]) {
            $data[gettype($from) . ' ' . var_export($from, true)] = [$from, $to];
        }

        return $data;
    }

    /**
     * @param $baseValue
     * @param $expectedValue
     *
     * @dataProvider compatibleValueProvider
     */
    public function testThatItDoesConvertCompatibleValues($baseValue, $expectedValue)
    {
        self::assertSame($expectedValue, (new IntegerConverter())->parse($baseValue));
    }


}