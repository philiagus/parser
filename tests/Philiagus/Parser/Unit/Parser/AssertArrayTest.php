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
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Parser\AssertArray;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Index;
use Philiagus\Parser\Path\Key;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Path\Root;
use Philiagus\Test\Parser\Provider\DataProvider;
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
        $this->expectException(ParsingException::class);
        (new AssertArray())->parse($value);
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

        $parser = $this->prophesize(Parser::class);
        foreach ($array as $key => $value) {
            $parser->execute($value, Argument::type(Index::class))->shouldBeCalledOnce()->willReturn($value);
        }

        /** @var Parser $valueParser */
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

        $parser = $this->prophesize(Parser::class);
        foreach ($array as $key => $value) {
            if ($value instanceof Exception) {
                $parser->execute($value, Argument::type(Index::class))
                    ->shouldBeCalledOnce()
                    ->willThrow($value);
            } else {
                $parser->execute($value, Argument::type(Index::class))->shouldBeCalledOnce()->willReturn($value);
            }
        }

        /** @var Parser $valueParser */
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

        $parser = $this->prophesize(Parser::class);
        foreach ($array as $key => $value) {
            $parser->execute($key, Argument::type(Key::class))->shouldBeCalledOnce()->willReturn($key);
        }

        /** @var Parser $keyParser */
        $keyParser = $parser->reveal();

        (new AssertArray())->withEachKey($keyParser)->parse($array);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeys(): void
    {
        $keyParser = $this->prophesize(Parser::class);
        $keyParser->execute(['a', 2, 'b', 0], Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        /** @var Parser $parser */
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

        $parser = $this->prophesize(Parser::class);
        foreach ($array as $key => $value) {
            if ($value instanceof Exception) {
                $parser->execute($key, Argument::type(Key::class))
                    ->shouldBeCalledOnce()
                    ->willThrow($value);
            } else {
                $parser->execute($key, Argument::type(Key::class))->shouldBeCalledOnce()->willReturn($key);
            }
        }

        /** @var Parser $keyParser */
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
        $parser = $this->prophesize(Parser::class);
        $parser->execute(2, Argument::type(MetaInformation::class))->shouldBeCalledOnce();

        /** @var Parser $lengthParser */
        $lengthParser = $parser->reveal();

        (new AssertArray())->withLength($lengthParser)->parse([1, 2]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithLengthException(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute(2, Argument::type(MetaInformation::class))
            ->shouldBeCalledOnce()
            ->willThrow(new ParsingException(2, 'message', new Root('root')));

        /** @var Parser $lengthParser */
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
        $parser = $this->prophesize(Parser::class);
        $parser->execute('value', Argument::type(Index::class))->shouldBeCalledOnce();

        /** @var Parser $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())
            ->withKeyHavingValue('key', $valueParser)
            ->parse(['key' => 'value']);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithKeyHavingValueMissingElement(): void
    {
        $parser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };

        $this->expectException(ParsingException::class);
        (new AssertArray())
            ->withKeyHavingValue('key', $parser)
            ->parse([]);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideInvalidArrayKeys(): array
    {
        return DataProvider::provide((int) ~(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideValidArrayKeys(): array
    {
        return DataProvider::provide(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER);
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
        (new AssertArray())->withKeyHavingValue($notStringInt, $parser);
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
        $parser = $this->prophesize(Parser::class);
        $parser->execute('value', Argument::type(Index::class))->shouldBeCalledOnce();

        /** @var Parser $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())
            ->withKeyHavingValue($stringInt, $valueParser)
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
            ->withKeyHavingValue('key', (new AssertArray()), $message)
            ->parse([]);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithDefaultedElement(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('value', Argument::type(Index::class))->shouldBeCalledOnce();
        /** @var Parser $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())
            ->withDefaultedElement('key', 'default', $valueParser)
            ->parse(['key' => 'value'], new Root('root'));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithMissingDefaultedElement(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('default', Argument::type(Index::class))->shouldBeCalledOnce();
        /** @var Parser $valueParser */
        $valueParser = $parser->reveal();
        (new AssertArray())
            ->withDefaultedElement('key', 'default', $valueParser)
            ->parse([], new Root('root'));
    }

    /**
     * @param $notStringInt
     *
     * @dataProvider provideInvalidArrayKeys
     * @throws ParserConfigurationException
     */
    public function testWithDefaultedElementWrongConfiguration($notStringInt): void
    {
        $parser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };
        $this->expectException(ParserConfigurationException::class);
        (new AssertArray())->withDefaultedElement($notStringInt, 'default', $parser);
    }

    /**
     * @param $stringInt
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidArrayKeys
     */
    public function testAcceptingValidKeysForWithDefaultedElement($stringInt): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('value', Argument::type(Index::class))->shouldBeCalledOnce();
        /** @var Parser $valueParser */
        $valueParser = $parser->reveal();

        (new AssertArray())
            ->withDefaultedElement($stringInt, 'default', $valueParser)
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
        (new AssertArray())->withTypeExceptionMessage($message)->parse('no');
    }

}