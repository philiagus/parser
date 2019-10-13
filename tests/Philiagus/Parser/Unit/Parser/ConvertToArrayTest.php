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
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ConvertToArray;
use Philiagus\Test\Parser\Provider\DataProvider;
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
        return DataProvider::provide((int) ~DataProvider::TYPE_ARRAY);
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
        return DataProvider::provide(DataProvider::TYPE_ARRAY);
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
        $result = (new ConvertToArray())->convertNonArraysWithArrayCast()->parse($value);
        self::assertIsArray($result);
        self::assertCount(count((array) $value), $result);
        if (count($result)) {
            if (is_object($value)) {
                self::assertSame((array) $value, $result);
            } else {
                DataProvider::assertSame($value, $result[0]);
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
        $result = (new ConvertToArray())->convertNonArraysWithKey('key')->parse($value);
        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertTrue(array_key_exists('key', $result));
        DataProvider::assertSame($value, $result['key']);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidKeys(): array
    {
        return DataProvider::provide(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER);
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
            (new ConvertToArray())->convertNonArraysWithKey($key)->parse('value')
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidKeys(): array
    {
        return DataProvider::provide((int) ~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_STRING));
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
        (new ConvertToArray())->convertNonArraysWithKey($wrongKey);
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
        $child = $this->prophesize(Parser::class);
        $child->execute('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');

        /** @var Parser $childParser */
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
        $child = $this->prophesize(Parser::class);
        $child->execute('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');

        /** @var Parser $childParser */
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
        $childParser = new class() extends Parser
        {
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
        $child = $this->prophesize(Parser::class);
        $child->execute('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        /** @var Parser $childParser */
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
        $child = $this->prophesize(Parser::class);
        $child->execute('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');

        /** @var Parser $childParser */
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
        $childParser = new class() extends Parser
        {
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
        (new ConvertToArray())->withTypeExceptionMessage($msg)->parse(false);
    }

    /**
     * @return array
     */
    public function provideWithKeyConvertingValueExceptionCases(): array
    {
        return [
            'replaced string' => ['Key {key}', 'Key \'key\'', 'key'],
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

}