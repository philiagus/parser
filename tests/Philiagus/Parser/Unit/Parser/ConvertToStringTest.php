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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ConvertToString;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ConvertToStringTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertInstanceOf(Parser::class, new ConvertToString());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide(
                DataProvider::TYPE_RESOURCE |
                DataProvider::TYPE_NAN |
                DataProvider::TYPE_INFINITE |
                DataProvider::TYPE_NULL |
                DataProvider::TYPE_ARRAY
            ) + [
                'object without __toString' => [new stdClass()],
            ];
    }

    /**
     * @dataProvider provideInvalidValues
     *
     * @param $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testThatItBlocksInvalidValues($value): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertToString())->parse($value);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithTypeExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new ConvertToString())->withTypeExceptionMessage($msg)->parse(null);
    }

    /**
     * @param $invalid
     *
     * @dataProvider provideInvalidValues
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithTypeExceptionMessageReplace($invalid): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('type ' . gettype($invalid));
        (new ConvertToString())->withTypeExceptionMessage('type {type}')->parse($invalid);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValueWithExpectedStrings(): array
    {
        $tests = [];
        $providerCases = DataProvider::provide(
            DataProvider::TYPE_INTEGER |
            DataProvider::TYPE_FLOAT |
            DataProvider::TYPE_STRING |
            DataProvider::TYPE_BOOLEAN
        );
        foreach ($providerCases as $case => [$value]) {
            $tests[$case] = [$value, (string) $value];
        }

        $tests['object with __toString'] = [
            new class()
            {
                public function __toString()
                {
                    return 'my string';
                }
            },
            'my string',
        ];

        return $tests;
    }

    /**
     * @param $value
     * @param string $expectedString
     *
     * @dataProvider provideValueWithExpectedStrings
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItConvertsValidValues($value, string $expectedString): void
    {
        self::assertSame($expectedString, (new ConvertToString($value))->parse($value));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithImplodeOfArrays(): void
    {
        self::assertSame(
            '1.2.3.4',
            (new ConvertToString())->withImplodeOfArrays('.')->parse(['1', '2', '3', '4'])
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithImplodeOfArraysWithoutStringValues(): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertToString())->withImplodeOfArrays('.')->parse([1, 2, 3]);
    }

    public function provideForWithImplodeOfArraysExceptionMessage(): array
    {
        return [
            'test no replacer' => ['msg', 'msg', [1]],
            'test with index replacer' => ['index {key}', 'index 1', ['a', 1]],
            'test with index and type replacer' => ['index {key} type {type}', 'index \'a\' type integer', ['a', 'a' => 1]],
        ];
    }

    /**
     * @param string $msg
     * @param string $expected
     * @param array $values
     *
     * @dataProvider provideForWithImplodeOfArraysExceptionMessage
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithImplodeOfArraysExceptionMessage(string $msg, string $expected, array $values): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expected);
        (new ConvertToString())->withImplodeOfArrays('.', $msg)->parse($values);
    }

    public function provideBooleanConversionValues(): array
    {
        return [
            'true case' => ['yes', 'no', 'yes', true],
            'false case' => ['yes', 'no', 'no', false],
        ];
    }

    /**
     * @param string $true
     * @param string $false
     * @param string $expected
     * @param bool $input
     *
     * @dataProvider provideBooleanConversionValues
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithBooleanValues(string $true, string $false, string $expected, bool $input): void
    {
        self::assertSame($expected, (new ConvertToString())->withBooleanValues($true, $false)->parse($input));
    }
}