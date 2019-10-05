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
use Philiagus\Parser\Parser\ConvertInteger;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class ConverterIntegerTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new ConvertInteger()) instanceof Parser);
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
     */
    public function testThatItDoesNotAcceptIncompatibleValues($incompatibleValue)
    {
        self::expectException(ParsingException::class);
        (new ConvertInteger())->parse($incompatibleValue);
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
        self::assertSame($expectedValue, (new ConvertInteger())->parse($baseValue));
    }


}