<?php
declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ConvertArray;
use Philiagus\Parser\Type\AcceptsMixed;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ConvertArrayTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new ConvertArray()) instanceof Parser);
    }

    public function provideInvalidValues(): array
    {
        return DataProvider::provide(~DataProvider::TYPE_ARRAY);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testThatItBlocksNonArrayValues($value)
    {
        self::expectException(ParsingException::class);
        (new ConvertArray())->parse($value);
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
    public function testThatItAllowsArrayValues($value)
    {
        $result = (new ConvertArray())->parse($value);
        self::assertSame($value, $result);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testThatItConvertsValuesToArrays($value): void
    {
        $result = (new ConvertArray())->convertNonArraysWithArrayCast()->parse($value);
        self::assertIsArray($result);
        self::assertCount(count((array) $value), $result);
        if (count($result)) {
            if (is_object($value)) {
                self::assertSame((array) $value, $result);
            } elseif (is_float($value) && is_nan($value)) {
                self::assertNan($result[0]);
            } else {
                self::assertSame($value, $result[0]);
            }
        }
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testThatItConvertsValuesWithDedicatedStringKey($value): void
    {
        $result = (new ConvertArray())->convertNonArraysWithKey('key')->parse($value);
        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertTrue(array_key_exists('key', $result));
        if (is_float($value) && is_nan($value)) {
            self::assertNan($result['key']);
        } else {
            self::assertSame($value, $result['key']);
        }
    }

    public function provideValidKeys(): array
    {
        return DataProvider::provide(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER);
    }

    /**
     * @param $key
     *
     * @dataProvider provideValidKeys
     */
    public function testThatConvertNonArraysAcceptsStringsAndIntegers($key): void
    {
        self::assertSame(
            [$key => 'value'],
            (new ConvertArray())->convertNonArraysWithKey($key)->parse('value')
        );
    }

    public function provideInvalidKeys(): array
    {
        return DataProvider::provide(~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_STRING));
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
        self::expectException(ParserConfigurationException::class);
        (new ConvertArray())->convertNonArraysWithKey($wrongKey);
    }

    public function testTestThatItForcesKeysWithValue(): void
    {
        $parser = (new ConvertArray())->withDefaultedElement('forced', 1);
        $result = $parser->parse(['original' => 1]);
        self::assertSame(
            [
                'original' => 1,
                'forced' => 1,
            ],
            $result
        );
    }

    public function testTestThatForcedKeysDoNotOverwriteExistingKeys(): void
    {
        $parser = (new ConvertArray())->withDefaultedElement('forced', 1);
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
     * @dataProvider provideValidKeys
     */
    public function testThatForcedKeysAcceptIntegerAndString($key): void
    {
        $parser = (new ConvertArray())->withDefaultedElement($key, 'value');
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
        self::expectException(ParserConfigurationException::class);
        (new ConvertArray())->withDefaultedElement($key, 'value');
    }

    public function testThatForcedKeysUseParser(): void
    {
        $childParser = $this->prophesize(AcceptsMixed::class);
        $childParser->parse('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        $parser = (new ConvertArray())->withDefaultedElement('key', 'value', $childParser->reveal());
        $result = $parser->parse([]);
        self::assertSame(
            [
                'key' => 'parsedValue',
            ],
            $result
        );
    }

    public function testThatForcedKeysUseParserEvenIfValueIsAlreadyPresent(): void
    {
        $childParser = $this->prophesize(AcceptsMixed::class);
        $childParser->parse('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        $parser = (new ConvertArray())->withDefaultedElement('key', 'will not be used', $childParser->reveal());
        $result = $parser->parse(['key' => 'value']);
        self::assertSame(
            [
                'key' => 'parsedValue',
            ],
            $result
        );
    }

    public function testWithElementWhitelist(): void
    {
        $parser = (new ConvertArray())->withElementWhitelist(['key1', 2]);
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
     */
    public function testThatElementWhitelistAcceptsIntegersAndStrings($key): void
    {
        $parser = (new ConvertArray())->withElementWhitelist([$key]);
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
    public function testThatElementWhitelistDoesNotAcceptNonIntegerString($key): void
    {
        self::expectException(ParserConfigurationException::class);
        (new ConvertArray())->withElementWhitelist([$key]);
    }

    public function testThatWhitelistIgnoresIfKeysAreNotPresent(): void
    {
        $parser = (new ConvertArray())->withElementWhitelist(['ignored', 'as well']);
        self::assertSame([], $parser->parse(['something', 'or', 'other']));
    }

    public function testThatForcedElementsMustExist(): void
    {
        $childParser = $this->prophesize(AcceptsMixed::class);
        $parser = (new ConvertArray())->withElement('key', $childParser->reveal());
        self::expectException(ParsingException::class);
        $parser->parse([]);
    }

    public function testThatForcedElementsAreParsedAndOverwriteReturn(): void
    {
        $childParser = $this->prophesize(AcceptsMixed::class);
        $childParser->parse('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        $parser = (new ConvertArray())->withElement('key', $childParser->reveal());
        self::assertSame(
            ['key' => 'parsedValue'],
            $parser->parse(['key' => 'value'])
        );
    }

    /**
     * @param $key
     * @dataProvider provideValidKeys
     * @throws ParserConfigurationException
     */
    public function testThatForcedElementKeysCanBeIntegerOrString($key): void
    {
        $childParser = $this->prophesize(AcceptsMixed::class);
        $childParser->parse('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        $parser = (new ConvertArray())->withElement($key, $childParser->reveal());
        self::assertSame(
            [$key => 'parsedValue'],
            $parser->parse([$key => 'value'])
        );
    }

    /**
     * @param $key
     * @dataProvider provideInvalidKeys
     * @throws ParserConfigurationException
     */
    public function testThatForcedElementKeysMustBeIntegerOrString($key): void
    {
        $childParser = $this->prophesize(AcceptsMixed::class);
        self::expectException(ParserConfigurationException::class);
        $parser = (new ConvertArray())->withElement($key, $childParser->reveal());
    }


}