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
use Philiagus\Parser\Parser\ConvertToInteger;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ConvertToIntegerTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new ConvertToInteger()) instanceof Parser);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideIncompatibleValues(): array
    {
        return
            array_merge(
                DataProvider::provide((int) ~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT | DataProvider::TYPE_STRING)),
                [
                    'string non numeric' => ['abc'],
                    'string almost numeric' => ['0abc'],
                    'float overflow' => [PHP_INT_MAX + .5],
                    'float underflow' => [PHP_INT_MIN - .5],
                ]
            );
    }

    /**
     * @param $incompatibleValue
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideIncompatibleValues
     */
    public function testThatItDoesNotAcceptIncompatibleValues($incompatibleValue): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertToInteger())->parse($incompatibleValue);
    }

    /**
     * @return array
     */
    public function compatibleValueProvider(): array
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
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider compatibleValueProvider
     */
    public function testThatItDoesConvertCompatibleValues($baseValue, $expectedValue): void
    {
        self::assertSame($expectedValue, (new ConvertToInteger())->parse($baseValue));
    }

    public function provideExceptionMessageData(): array
    {
        return [
            'string' => ['its {type}', 'its string', 'abc'],
            'object' => ['its {type}', 'its object', new stdClass()],
            'array' => ['its {type}', 'its array', []],
        ];
    }

    /**
     * @param string $baseMsg
     * @param string $expected
     * @param $value
     *
     * @dataProvider provideExceptionMessageData
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testExceptionMessage(string $baseMsg, string $expected, $value): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expected);
        (new ConvertToInteger())->withExceptionMessage($baseMsg)->parse($value);
    }

}