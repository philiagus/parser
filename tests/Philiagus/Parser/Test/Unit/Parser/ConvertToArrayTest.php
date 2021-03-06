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
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ConvertToArray;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

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
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideInvalidValues
     */
    public function testThatItBlocksNonArrayValues($value): void
    {
        $this->expectException(ParsingException::class);
        (new ConvertToArray())->parse($value);
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
        $result = (new ConvertToArray())->parse($value);
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
            ->setConvertNonArrays(ConvertToArray::CONVERSION_ARRAY_CAST)
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
        $result = (new ConvertToArray())
            ->setConvertNonArrays(ConvertToArray::CONVERSION_ARRAY_WITH_KEY, 'key')
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
                ->setConvertNonArrays(ConvertToArray::CONVERSION_ARRAY_WITH_KEY, $key)
                ->parse('value')
        );
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatConvertNonArraysThrowsExceptionOnUnknownConversion(): void
    {
        $this->expectException(ParserConfigurationException::class);
        ConvertToArray::new()->setConvertNonArrays(66);
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatConvertNonArraysCannotBeOverwritten(): void
    {
        $parser = ConvertToArray::new()
            ->setConvertNonArrays(ConvertToArray::CONVERSION_ARRAY_WITH_KEY, 'asdf');
        $this->expectException(ParserConfigurationException::class);
        $parser->setConvertNonArrays(ConvertToArray::CONVERSION_ARRAY_WITH_KEY, 'asdf');
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
            ->setConvertNonArrays(ConvertToArray::CONVERSION_ARRAY_WITH_KEY, $wrongKey);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testTestThatItForcesKeysWithValue(): void
    {
        $parser = (new ConvertToArray())->withDefaultedKey('forced', 1);
        $result = $parser->parse(['original' => 1]);
        self::assertSame(
            [
                'original' => 1,
                'forced' => 1,
            ],
            $result
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testTestThatForcedKeysDoNotOverwriteExistingKeys(): void
    {
        $parser = (new ConvertToArray())->withDefaultedKey('forced', 1);
        $result = $parser->parse(['forced' => 666]);
        self::assertSame(
            [
                'forced' => 666,
            ],
            $result
        );
    }

    /**
     * @param $key
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidKeys
     */
    public function testThatForcedKeysAcceptIntegerAndString($key): void
    {
        $parser = (new ConvertToArray())->withDefaultedKey($key, 'value');
        self::assertSame(
            [
                $key => 'value',
            ],
            $parser->parse([])
        );
    }

    /**
     * @param $key
     *
     * @dataProvider provideInvalidKeys
     * @throws ParserConfigurationException
     */
    public function testThatForcedKeysDoNotExceptNonIntegerOrString($key): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new ConvertToArray())->withDefaultedKey($key, 'value');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatForcedKeysUseParser(): void
    {
        $child = $this->prophesize(ParserContract::class);
        $child->parse('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');

        /** @var ParserContract $childParser */
        $childParser = $child->reveal();
        $parser = (new ConvertToArray())->withDefaultedKey('key', 'value', $childParser);
        $result = $parser->parse([]);
        self::assertSame(
            [
                'key' => 'parsedValue',
            ],
            $result
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatForcedKeysUseParserEvenIfValueIsAlreadyPresent(): void
    {
        $child = $this->prophesize(ParserContract::class);
        $child->parse('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');

        /** @var ParserContract $childParser */
        $childParser = $child->reveal();
        $parser = (new ConvertToArray())->withDefaultedKey('key', 'will not be used', $childParser);
        $result = $parser->parse(['key' => 'value']);
        self::assertSame(
            [
                'key' => 'parsedValue',
            ],
            $result
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeyWhitelist(): void
    {
        $parser = (new ConvertToArray())->withKeyWhitelist(['key1', 2]);
        self::assertSame(
            [
                'key1' => 123,
                2 => 234,
            ],
            $parser->parse([
                'gone' => 'this will be removed',
                'key1' => 123,
                'gone2' => 'this will be removed',
                2 => 234,
                'gone3' => 'this will be removed',
            ])
        );
    }

    /**
     * @param $key
     *
     * @dataProvider provideValidKeys
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatKeyWhitelistAcceptsIntegersAndStrings($key): void
    {
        $parser = (new ConvertToArray())->withKeyWhitelist([$key]);
        self::assertSame(
            [
                $key => 'exists',
            ],
            $parser->parse([
                $key => 'exists',
                $key . 'f' => 'does not exist',
            ])
        );
    }

    /**
     * @param $key
     *
     * @dataProvider provideInvalidKeys
     * @throws ParserConfigurationException
     */
    public function testThatKeyWhitelistDoesNotAcceptNonIntegerString($key): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new ConvertToArray())->withKeyWhitelist([$key]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatWhitelistIgnoresIfKeysAreNotPresent(): void
    {
        $parser = (new ConvertToArray())->withKeyWhitelist(['ignored', 'as well']);
        self::assertSame([], $parser->parse(['something', 'or', 'other']));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatForcedKeysMustExist(): void
    {
        $childParser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };
        $parser = (new ConvertToArray())->withKey('key', $childParser);
        $this->expectException(ParsingException::class);
        $parser->parse([]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatForcedKeysAreParsedAndOverwriteReturn(): void
    {
        $child = $this->prophesize(ParserContract::class);
        $child->parse('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        /** @var ParserContract $childParser */
        $childParser = $child->reveal();
        $parser = (new ConvertToArray())->withKey('key', $childParser);
        self::assertSame(
            ['key' => 'parsedValue'],
            $parser->parse(['key' => 'value'])
        );
    }

    /**
     * @param $key
     *
     * @dataProvider provideValidKeys
     * @throws ParserConfigurationException*@throws ParsingException
     * @throws ParsingException
     */
    public function testThatForcedKeysCanBeIntegerOrString($key): void
    {
        $child = $this->prophesize(ParserContract::class);
        $child->parse('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');

        /** @var ParserContract $childParser */
        $childParser = $child->reveal();
        $parser = (new ConvertToArray())->withKey($key, $childParser);
        self::assertSame(
            [$key => 'parsedValue'],
            $parser->parse([$key => 'value'])
        );
    }

    /**
     * @param $key
     *
     * @dataProvider provideInvalidKeys
     * @throws ParserConfigurationException
     */
    public function testThatForcedKeysMustBeIntegerOrString($key): void
    {
        $childParser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };
        $this->expectException(ParserConfigurationException::class);
        (new ConvertToArray())->withKey($key, $childParser);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithSequentialKeysConversion(): void
    {
        $input = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
        self::assertSame(
            array_values($input),
            (new ConvertToArray())
                ->withSequentialKeys()
                ->parse($input)
        );
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
        (new ConvertToArray())->overwriteTypeExceptionMessage($msg)->parse(false);
    }

    /**
     * @return array
     */
    public function provideWithKeyConvertingValueExceptionCases(): array
    {
        return [
            'replaced string' => ['Key \'{key}\'', 'Key \'key\'', 'key'],
            'replaced int' => ['Key {key}', 'Key 1', 1],
            'fixed' => ['Key', 'Key', 'key'],
        ];
    }

    /**
     * @param string $exception
     * @param string $expected
     * @param $key
     *
     * @dataProvider provideWithKeyConvertingValueExceptionCases
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeyConvertingValueExceptionMessage(string $exception, string $expected, $key): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expected);
        (new ConvertToArray())->withKey($key, (new ConvertToArray()), $exception)->parse([]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithEachKeyConversion(): void
    {
        self::assertSame(
            [
                'something 0' => 1,
                'another thing 1' => 2,
                '0 2' => 3,
                '1 3' => 4,
                '2 4' => 5,
                '3 5' => 6,
            ],
            (new ConvertToArray())
                ->withEachKey(
                    new class() extends Parser {
                        private $counter = 0;

                        protected function execute($value, Path $path)
                        {
                            return $value . ' ' . $this->counter++;
                        }
                    }
                )
                ->parse(
                    [
                        'something' => 1,
                        'another thing' => 2,
                        3,
                        4,
                        5,
                        6,
                    ]
                )
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatLastValueWithSameKeyIsPreserved(): void
    {
        self::assertSame(
            ['last' => 7],
            (new ConvertToArray())
                ->withEachKey(
                    new class() extends Parser {
                        protected function execute($value, Path $path)
                        {
                            return 'last';
                        }
                    }
                )
                ->parse(
                    [
                        'something' => 1,
                        'another thing' => 2,
                        3,
                        4,
                        5,
                        6,
                        7,
                    ]
                )
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatConvertOfEachValueChangesValues(): void
    {
        self::assertSame(
            [
                'key1' => '2a',
                4 => '3a',
                'foo' => 'somethinga',
            ],
            (new ConvertToArray())
                ->withEachValue(
                    new class() extends Parser {
                        protected function execute($value, Path $path)
                        {
                            return $value . 'a';
                        }
                    }
                )
                ->parse(
                    [
                        'key1' => 2,
                        4 => 3,
                        'foo' => 'something',
                    ]
                )
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideEachKeyCases(): array
    {
        return (new DataProvider(~DataProvider::TYPE_SCALAR))
            ->map(function($value) {
                return [
                    4,
                    $value,
                    '4 => ' . gettype($value),
                    '{oldKey} => {newType}',
                ];
            })
            ->provide(false);
    }

    /**
     * @param $oldKey
     * @param $newKey
     * @param string $expectedMessage
     * @param string $msg
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideEachKeyCases
     */
    public function testThatEachKeyDoesNotAcceptNonScalarValues(
        $oldKey,
        $newKey,
        string $expectedMessage,
        string $msg
    ): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse($oldKey, Argument::any())->willReturn($newKey);

        /** @var ParserContract $childParser */
        $childParser = $parser->reveal();
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);
        (new ConvertToArray())
            ->withEachKey($childParser, $msg)
            ->parse([$oldKey => 'a']);
    }

    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '5 integer integer 5'
        );
        (new ConvertToArray())
            ->overwriteTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse(5);
    }

}