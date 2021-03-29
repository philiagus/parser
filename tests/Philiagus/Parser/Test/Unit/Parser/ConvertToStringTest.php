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
use Philiagus\Parser\Parser\ConvertToString;
use PHPUnit\Framework\TestCase;

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
        return (new DataProvider(
            DataProvider::TYPE_RESOURCE |
            DataProvider::TYPE_NAN |
            DataProvider::TYPE_INFINITE |
            DataProvider::TYPE_NULL |
            DataProvider::TYPE_ARRAY
        ))
            ->addCase('object without __toString', new \stdClass())
            ->provide();
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
        (new ConvertToString())->setTypeExceptionMessage($msg)->parse(null);
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
        (new ConvertToString())->setTypeExceptionMessage('type {value.gettype}')->parse($invalid);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValueWithExpectedStrings(): array
    {

        return (new DataProvider(
            DataProvider::TYPE_INTEGER |
            DataProvider::TYPE_FLOAT |
            DataProvider::TYPE_STRING |
            DataProvider::TYPE_BOOLEAN
        ))
            ->addCase(
                'object with __toString',
                new class() {
                    public function __toString()
                    {
                        return 'my string';
                    }
                }
            )
            ->map(function($value) {
                return [$value, (string) $value];
            })
            ->provide(false);
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
            (new ConvertToString())->setImplodeOfArrays('.')->parse(['1', '2', '3', '4'])
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithImplodeOfArraysWithoutStringValues(): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertToString())->setImplodeOfArrays('.')->parse([1, 2, 3]);
    }

    public function provideForWithImplodeOfArraysExceptionMessage(): array
    {
        return [
            'test no replacer' => ['msg', 'msg', [1]],
            'test with index replacer' => ['index {key}', 'index 1', ['a', 1]],
            'test with index and type replacer' => ['index {key} type {culprit.type}', 'index a type integer', ['a', 'a' => 1]],
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
        (new ConvertToString())->setImplodeOfArrays('.', $msg)->parse($values);
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
        self::assertSame($expected, (new ConvertToString())->setBooleanValues($true, $false)->parse($input));
    }

    public function testAllSetTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'Object object<stdClass> object<stdClass>'
        );
        (new ConvertToString())
            ->setTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse((object) []);
    }

    public function testAllSetImplodeOfArraysExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'Array array array<integer,mixed>(2) | 1 integer integer 1 | 1 integer integer 1'
        );
        (new ConvertToString())
            ->setImplodeOfArrays('', '{value} {value.type} {value.debug} | {key} {key.type} {key.debug} | {culprit} {culprit.type} {culprit.debug}')
            ->parse(['a', 1]);
    }
}