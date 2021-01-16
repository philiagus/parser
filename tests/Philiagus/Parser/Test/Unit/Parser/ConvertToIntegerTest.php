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
use Philiagus\Parser\Parser\ConvertToInteger;
use PHPUnit\Framework\TestCase;

class ConvertToIntegerTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new ConvertToInteger()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideIncompatibleValues(): array
    {
        return (new DataProvider(~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT | DataProvider::TYPE_STRING)))
            ->addCase('non numeric', 'abc')
            ->addCase('almost numeric', '0abc')
            ->addCase('float overflow', PHP_INT_MAX + .5)
            ->addCase('float underflow', PHP_INT_MIN - .5)
            ->provide();
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
            'string' => ['its {value.gettype}', 'its string', 'abc'],
            'object' => ['its {value.gettype}', 'its object', new \stdClass()],
            'array' => ['its {value.gettype}', 'its array', []],
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
        (new ConvertToInteger())->overwriteTypeExceptionMessage($baseMsg)->parse($value);
    }


    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '. string string<ASCII>(1)"."'
        );
        (new ConvertToInteger())
            ->overwriteTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse('.');
    }

}