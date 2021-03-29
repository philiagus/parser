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
use Philiagus\Parser\Parser\AssertArray;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Index;
use Philiagus\Parser\Path\Key;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Path\Root;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * Class AssertArrayTest
 *
 * @package Philiagus\Test\Parser\Unit\Parser
 */
class AssertArrayTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertArray()) instanceof Parser);
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
        (new AssertArray())->parse($value);
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
        $result = (new AssertArray())->parse($value);
        self::assertSame($value, $result);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithEachValue(): void
    {
        $array = [
            'key1' => 'one',
            'key2' => 'two',
            'key3' => ['array'],
        ];

        $parser = $this->prophesize(ParserContract::class);
        foreach ($array as $key => $value) {
            $parser->parse($value, Argument::type(Index::class))->shouldBeCalledOnce()->willReturn($value);
        }

        /** @var ParserContract $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())->withEachValue($valueParser)->parse($array);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithEachValueException(): void
    {
        $exception = new ParsingException('value', 'message', new Root('root'));
        $array = [
            'key1' => 'one',
            'key2' => 'two',
            'key3' => ['array'],
            'key4' => $exception,
        ];

        $parser = $this->prophesize(ParserContract::class);
        foreach ($array as $key => $value) {
            if ($value instanceof \Exception) {
                $parser->parse($value, Argument::type(Index::class))
                    ->shouldBeCalledOnce()
                    ->willThrow($value);
            } else {
                $parser->parse($value, Argument::type(Index::class))->shouldBeCalledOnce()->willReturn($value);
            }
        }

        /** @var ParserContract $valueParser */
        $valueParser = $parser->reveal();

        $this->expectException(ParsingException::class);
        (new AssertArray())->withEachValue($valueParser)->parse($array);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithEachKey(): void
    {
        $array = [
            'key1' => 'one',
            'key2' => 'two',
            'key3' => ['array'],
        ];

        $parser = $this->prophesize(ParserContract::class);
        foreach ($array as $key => $value) {
            $parser->parse($key, Argument::type(Key::class))->shouldBeCalledOnce()->willReturn($key);
        }

        /** @var ParserContract $keyParser */
        $keyParser = $parser->reveal();

        (new AssertArray())->withEachKey($keyParser)->parse($array);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeys(): void
    {
        $keyParser = $this->prophesize(ParserContract::class);
        $keyParser->parse(['a', 2, 'b', 0], Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var ParserContract $parser */
        $parser = $keyParser->reveal();

        (new AssertArray())->withKeys($parser)->parse(
            [
                'a' => 1,
                2 => 2,
                'b' => 3,
                0 => 4,
            ]
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeysException(): void
    {
        $exception = new ParsingException('value', 'message', new Root('root'));
        $array = [
            'key1' => 'one',
            'key2' => 'two',
            'key3' => ['array'],
            'key4' => $exception,
        ];

        $parser = $this->prophesize(ParserContract::class);
        foreach ($array as $key => $value) {
            if ($value instanceof \Exception) {
                $parser->parse($key, Argument::type(Key::class))
                    ->shouldBeCalledOnce()
                    ->willThrow($value);
            } else {
                $parser->parse($key, Argument::type(Key::class))->shouldBeCalledOnce()->willReturn($key);
            }
        }

        /** @var ParserContract $keyParser */
        $keyParser = $parser->reveal();

        $this->expectException(ParsingException::class);
        (new AssertArray())->withEachKey($keyParser)->parse($array);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithLength(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse(2, Argument::type(MetaInformation::class))->shouldBeCalledOnce();

        /** @var ParserContract $lengthParser */
        $lengthParser = $parser->reveal();

        (new AssertArray())->withLength($lengthParser)->parse([1, 2]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithLengthException(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse(2, Argument::type(MetaInformation::class))
            ->shouldBeCalledOnce()
            ->willThrow(new ParsingException(2, 'message', new Root('root')));

        /** @var ParserContract $lengthParser */
        $lengthParser = $parser->reveal();

        $this->expectException(ParsingException::class);
        (new AssertArray())->withLength($lengthParser)->parse([1, 2]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeyHavingValue(): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('value', Argument::type(Index::class))->shouldBeCalledOnce();

        /** @var ParserContract $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())
            ->withKey('key', $valueParser)
            ->parse(['key' => 'value']);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeyHavingValueMissingKey(): void
    {
        $parser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };

        $this->expectException(ParsingException::class);
        (new AssertArray())
            ->withKey('key', $parser)
            ->parse([]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidArrayKeys(): array
    {
        return (new DataProvider(~(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER)))->provide();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidArrayKeys(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER))->provide();
    }

    /**
     * @param $notStringInt
     *
     * @dataProvider provideInvalidArrayKeys
     * @throws ParserConfigurationException
     */
    public function testWithKeyHavingValueWithWrongConfiguration($notStringInt): void
    {
        $parser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };
        $this->expectException(ParserConfigurationException::class);
        (new AssertArray())->withKey($notStringInt, $parser);
    }

    /**
     * @param $stringInt
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidArrayKeys
     */
    public function testWithKeyHavingValueAcceptingValidKeys($stringInt): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('value', Argument::type(Index::class))->shouldBeCalledOnce();

        /** @var ParserContract $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())
            ->withKey($stringInt, $valueParser)
            ->parse([$stringInt => 'value'], new Root('root'));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeyHavingValueExceptionMessageOnMissingKey(): void
    {
        $message = 'This is an error message';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($message);
        (new AssertArray())
            ->withKey('key', (new AssertArray()), $message)
            ->parse([]);
    }

    /**
     * @param $key
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidArrayKeys
     */
    public function testWithDefaultedKey($key): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('value', Argument::type(Index::class))->shouldBeCalledOnce();
        /** @var ParserContract $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())
            ->withDefaultedKey($key, 'default', $valueParser)
            ->parse([$key => 'value'], new Root('root'));
    }

    /**
     * @param $key
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidArrayKeys
     */
    public function testWithMissingDefaultedKey($key): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('default', Argument::type(Index::class))->shouldBeCalledOnce();
        /** @var ParserContract $valueParser */
        $valueParser = $parser->reveal();
        (new AssertArray())
            ->withDefaultedKey($key, 'default', $valueParser)
            ->parse(['does not exist' . $key => 1], new Root('root'));
    }

    /**
     * @param $notStringInt
     *
     * @dataProvider provideInvalidArrayKeys
     * @throws ParserConfigurationException
     */
    public function testWithDefaultedKeyWrongConfiguration($notStringInt): void
    {
        $parser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };
        $this->expectException(ParserConfigurationException::class);
        (new AssertArray())->withDefaultedKey($notStringInt, 'default', $parser);
    }

    /**
     * @param $stringInt
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidArrayKeys
     */
    public function testAcceptingValidKeysForWithDefaultedKey($stringInt): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('value', Argument::type(Index::class))->shouldBeCalledOnce();
        /** @var ParserContract $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())
            ->withDefaultedKey($stringInt, 'default', $valueParser)
            ->parse([$stringInt => 'value'], new Root('root'));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItAllowsSequentialArrays(): void
    {
        $array = [1, 2, 3, 4, 5];
        $after = (new AssertArray())->withSequentialKeys()->parse($array);
        self::assertSame($array, $after);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItBlocksNotSequentialArrays(): void
    {
        $array = [1 => 1, 2 => 2, 3 => 3];
        $this->expectException(ParsingException::class);
        (new AssertArray())->withSequentialKeys()->parse($array);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatSequentialArraysUseExceptionMessage(): void
    {
        $message = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($message);
        (new AssertArray())->withSequentialKeys($message)->parse(['a' => 1]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithTypeExceptionMessage(): void
    {
        $message = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($message);
        (new AssertArray())->setTypeExceptionMessage($message)->parse('no');
    }

    public function testAllSetTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'hello string string<ASCII>(5)"hello"'
        );
        (new AssertArray())
            ->setTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse('hello');
    }

    public function testAllWithKeyExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'Array array array<integer,string>(3) | hello string string<ASCII>(5)"hello"'
        );
        (new AssertArray())
            ->withKey('hello', new AssertArray(), '{value} {value.type} {value.debug} | {key} {key.type} {key.debug}')
            ->parse(['a', '', '']);
    }

    public function testAllWithSequentialKeysExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'Array array array<integer,string>(3)'
        );
        (new AssertArray())
            ->withSequentialKeys('{value} {value.type} {value.debug}')
            ->parse(['a', 5 => '', '']);
    }

    /**
     * @param $key
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidArrayKeys
     */
    public function testWithOptionalKey($key): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser->parse('value', Argument::type(Index::class))->shouldBeCalledOnce();
        $parser->parse(Argument::not('value'), Argument::any())->shouldNotBeCalled();
        $parser = $parser->reveal();
        (new AssertArray())
            ->withOptionalKey($key, $parser)
            ->withOptionalKey('does not exist' . $key, $parser)
            ->parse([
                $key => 'value',
                'not used' . $key => 'value'
            ]);
    }

    /**
     * @param $key
     *
     * @dataProvider provideInvalidArrayKeys
     */
    public function testWithOptionalKeyException($key): void
    {
        $parser = $this->prophesize(ParserContract::class);
        $parser = $parser->reveal();
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('Arrays only accept string or integer keys');
        (new AssertArray())
            ->withOptionalKey($key, $parser);
    }

}