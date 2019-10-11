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

class AssertArrayTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertArray()) instanceof Parser);
    }

    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int)~DataProvider::TYPE_ARRAY);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testThatItBlocksNonArrayValues($value): void
    {
        self::expectException(ParsingException::class);
        (new AssertArray())->parse($value);
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
    public function testThatItAllowsArrayValues($value): void
    {
        $result = (new AssertArray())->parse($value);
        self::assertSame($value, $result);
    }

    public function testWithValues(): void
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

        (new AssertArray())->withValues($parser->reveal())->parse($array);
    }

    public function testWithValuesException(): void
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

        self::expectException(ParsingException::class);
        (new AssertArray())->withValues($parser->reveal())->parse($array);
    }

    public function testWithKeys(): void
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

        (new AssertArray())->withKeys($parser->reveal())->parse($array);
    }

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

        self::expectException(ParsingException::class);
        (new AssertArray())->withKeys($parser->reveal())->parse($array);
    }

    public function testWithLength(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute(2, Argument::type(MetaInformation::class))->shouldBeCalledOnce();

        (new AssertArray())->withLength($parser->reveal())->parse([1, 2]);
    }

    public function testWithLengthException(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute(2, Argument::type(MetaInformation::class))
            ->shouldBeCalledOnce()
            ->willThrow(new ParsingException(2, 'message', new Root('root')));

        self::expectException(ParsingException::class);
        (new AssertArray())->withLength($parser->reveal())->parse([1, 2]);
    }

    public function testWithElement(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('value', Argument::type(Index::class))->shouldBeCalledOnce();

        (new AssertArray())
            ->withElement('key', $parser->reveal())
            ->parse(['key' => 'value']);
    }

    public function testWithMissingElement(): void
    {
        $parser = $this->prophesize(Parser::class);

        self::expectException(ParsingException::class);
        (new AssertArray())
            ->withElement('key', $parser->reveal())
            ->parse([]);
    }

    public function provideInvalidArrayKeys(): array
    {
        return DataProvider::provide((int)~(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER));
    }

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
    public function testWithElementWrongConfiguration($notStringInt): void
    {
        $parser = $this->prophesize(Parser::class)->reveal();
        self::expectException(ParserConfigurationException::class);
        (new AssertArray())->withElement($notStringInt, $parser);
    }

    /**
     * @param $stringInt
     *
     * @dataProvider provideValidArrayKeys
     */
    public function testAcceptingValidKeys($stringInt): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('value', Argument::type(Index::class))->shouldBeCalledOnce();

        (new AssertArray())
            ->withElement($stringInt, $parser->reveal())
            ->parse([$stringInt => 'value'], new Root('root'));
    }

    public function testWithDefaultedElement(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('value', Argument::type(Index::class))->shouldBeCalledOnce();

        (new AssertArray())
            ->withDefaultedElement('key', 'default', $parser->reveal())
            ->parse(['key' => 'value'], new Root('root'));
    }

    public function testWithMissingDefaultedElement(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('default', Argument::type(Index::class))->shouldBeCalledOnce();

        (new AssertArray())
            ->withDefaultedElement('key', 'default', $parser->reveal())
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
        $parser = $this->prophesize(Parser::class)->reveal();
        self::expectException(ParserConfigurationException::class);
        (new AssertArray())->withDefaultedElement($notStringInt, 'default', $parser);
    }

    /**
     * @param $stringInt
     *
     * @dataProvider provideValidArrayKeys
     */
    public function testAcceptingValidKeysForWithDefaultedElement($stringInt): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute('value', Argument::type(Index::class))->shouldBeCalledOnce();

        (new AssertArray())
            ->withDefaultedElement($stringInt, 'default', $parser->reveal())
            ->parse([$stringInt => 'value'], new Root('root'));
    }

    public function testThatItAllowsSequentialArrays(): void
    {
        $array = [1,2,3,4,5];
        $after = (new AssertArray())->withSequentialKeys()->parse($array);
        self::assertSame($array, $after);
    }

    public function testThatItBlocksNotSequentialArrays(): void
    {
        $array = [1 => 1, 2 => 2, 3 => 3];
        self::expectException(ParsingException::class);
        (new AssertArray())->withSequentialKeys()->parse($array);
    }

}