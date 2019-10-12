<?php
declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit\Parser;

use Exception;
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
     * @throws Exception
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
        self::expectException(ParsingException::class);
        (new ConvertToArray())->parse($value);
    }

    /**
     * @return array
     * @throws Exception
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
        if (is_float($value) && is_nan($value)) {
            self::assertNan($result['key']);
        } else {
            self::assertSame($value, $result['key']);
        }
    }

    /**
     * @return array
     * @throws Exception
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
     * @throws Exception
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
        self::expectException(ParserConfigurationException::class);
        (new ConvertToArray())->convertNonArraysWithKey($wrongKey);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testTestThatItForcesKeysWithValue(): void
    {
        $parser = (new ConvertToArray())->withDefaultedElement('forced', 1);
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
        $parser = (new ConvertToArray())->withDefaultedElement('forced', 1);
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
        $parser = (new ConvertToArray())->withDefaultedElement($key, 'value');
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
        (new ConvertToArray())->withDefaultedElement($key, 'value');
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatForcedKeysUseParser(): void
    {
        $childParser = $this->prophesize(Parser::class);
        $childParser->execute('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        $parser = (new ConvertToArray())->withDefaultedElement('key', 'value', $childParser->reveal());
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
        $childParser = $this->prophesize(Parser::class);
        $childParser->execute('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        $parser = (new ConvertToArray())->withDefaultedElement('key', 'will not be used', $childParser->reveal());
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
    public function testWithElementWhitelist(): void
    {
        $parser = (new ConvertToArray())->withElementWhitelist(['key1', 2]);
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
    public function testThatElementWhitelistAcceptsIntegersAndStrings($key): void
    {
        $parser = (new ConvertToArray())->withElementWhitelist([$key]);
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
        (new ConvertToArray())->withElementWhitelist([$key]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatWhitelistIgnoresIfKeysAreNotPresent(): void
    {
        $parser = (new ConvertToArray())->withElementWhitelist(['ignored', 'as well']);
        self::assertSame([], $parser->parse(['something', 'or', 'other']));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatForcedElementsMustExist(): void
    {
        $childParser = $this->prophesize(Parser::class);
        $parser = (new ConvertToArray())->withElement('key', $childParser->reveal());
        self::expectException(ParsingException::class);
        $parser->parse([]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatForcedElementsAreParsedAndOverwriteReturn(): void
    {
        $childParser = $this->prophesize(Parser::class);
        $childParser->execute('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        $parser = (new ConvertToArray())->withElement('key', $childParser->reveal());
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
    public function testThatForcedElementKeysCanBeIntegerOrString($key): void
    {
        $childParser = $this->prophesize(Parser::class);
        $childParser->execute('value', Argument::type(Path::class))
            ->shouldBeCalledOnce()
            ->willReturn('parsedValue');
        $parser = (new ConvertToArray())->withElement($key, $childParser->reveal());
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
    public function testThatForcedElementKeysMustBeIntegerOrString($key): void
    {
        $childParser = $this->prophesize(Parser::class);
        self::expectException(ParserConfigurationException::class);
        (new ConvertToArray())->withElement($key, $childParser->reveal());
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


}