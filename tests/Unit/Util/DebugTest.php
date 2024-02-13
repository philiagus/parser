<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Util;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Util\Debug;

/**
 * @covers \Philiagus\Parser\Util\Debug
 */
class DebugTest extends TestBase
{
    public static function provideAnything(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))->provide();
    }

    public static function provideTypeDetection(): \Generator
    {
        yield 'true' => [true, 'true'];
        yield 'false' => [false, 'false'];
        yield 'NAN' => [NAN, 'NAN'];
        yield 'INF' => [INF, 'INF'];
        yield '-INF' => [-INF, '-INF'];
        yield 'integer' => [100, 'integer'];
        yield 'NULL' => [null, 'null'];
        yield 'resource' => [fopen('php://memory', 'r'), 'resource'];
        yield 'array' => [['array'], 'array'];

        foreach (
            [
                new \stdClass(),
                new \Exception(),
                new \DateTime(),
            ] as $object
        ) {
            $class = get_class($object);
            yield "object $class" => [$object, "object<$class>"];
        }
    }

    public static function provideStringify(): \Generator
    {
        yield 'integer ' . PHP_INT_MAX => [PHP_INT_MAX, 'integer ' . PHP_INT_MAX];
        yield 'integer 10' => [10, 'integer 10'];
        yield 'integer 0' => [0, 'integer 0'];
        yield 'integer -10' => [-10, 'integer -10'];
        yield 'integer ' . PHP_INT_MIN => [PHP_INT_MIN, 'integer ' . PHP_INT_MIN];
        yield 'float -99.9' => [-99.9, 'float -99.9'];
        yield 'float -0' => [-0.0, 'float -0'];
        yield 'float 0' => [0.0, 'float 0'];
        yield 'float 99.498' => [99.498, 'float 99.498'];
        yield 'float INF' => [INF, 'INF'];
        yield 'float -INF' => [-INF, '-INF'];
        yield 'float NAN' => [NAN, 'NAN'];
        yield 'boolean true' => [true, 'boolean true'];
        yield 'boolean false' => [false, 'boolean false'];
        yield 'null' => [null, 'null'];
        yield 'resource' => [fopen('php://memory', 'r'), 'resource'];
        yield 'array(0)' => [[], 'array(0)'];
        yield 'object<stdClass>' => [(object)[], 'object<stdClass>'];
        yield 'string<ASCII>(15)"012345678901234"' => ['012345678901234', 'string<ASCII>(15)"012345678901234"'];
        yield 'string<UTF-8>(2)"ü"' => ['ü', 'string<UTF-8>(2)"ü"'];
        yield 'string<binary>(1)' => [chr(252), 'string<binary>(1)'];
        yield "string<ASCII>(100)\"0000000000000000000000000000000\u{2026}\"" => [str_repeat('0', 100), "string<ASCII>(100)\"0000000000000000000000000000000\u{2026}\""];
        yield "string<binary>(30)" => [str_repeat("\xFF\xFE\xFD", 10), "string<binary>(30)"];
        yield "string<ASCII>(10)\"          \"" => ['          ', "string<ASCII>(10)\"          \""];
        yield "string<ASCII>(1)\"␀\"" => ["\x00", "string<ASCII>(1)\"␀\""];
        yield "string<ASCII>(1)\"␁\"" => ["\x01", "string<ASCII>(1)\"␁\""];
        yield "string<ASCII>(1)\"␂\"" => ["\x02", "string<ASCII>(1)\"␂\""];
        yield "string<ASCII>(1)\"␃\"" => ["\x03", "string<ASCII>(1)\"␃\""];
        yield "string<ASCII>(1)\"␄\"" => ["\x04", "string<ASCII>(1)\"␄\""];
        yield "string<ASCII>(1)\"␅\"" => ["\x05", "string<ASCII>(1)\"␅\""];
        yield "string<ASCII>(1)\"␆\"" => ["\x06", "string<ASCII>(1)\"␆\""];
        yield "string<ASCII>(1)\"␇\"" => ["\x07", "string<ASCII>(1)\"␇\""];
        yield "string<ASCII>(1)\"␈\"" => ["\x08", "string<ASCII>(1)\"␈\""];
        yield "string<ASCII>(1)\"␉\"" => ["\x09", "string<ASCII>(1)\"␉\""];
        yield "string<ASCII>(1)\"␊\"" => ["\x0A", "string<ASCII>(1)\"␊\""];
        yield "string<ASCII>(1)\"␋\"" => ["\x0B", "string<ASCII>(1)\"␋\""];
        yield "string<ASCII>(1)\"␌\"" => ["\x0C", "string<ASCII>(1)\"␌\""];
        yield "string<ASCII>(1)\"␍\"" => ["\x0D", "string<ASCII>(1)\"␍\""];
        yield "string<ASCII>(1)\"␎\"" => ["\x0E", "string<ASCII>(1)\"␎\""];
        yield "string<ASCII>(1)\"␏\"" => ["\x0F", "string<ASCII>(1)\"␏\""];
        yield "string<ASCII>(1)\"␐\"" => ["\x10", "string<ASCII>(1)\"␐\""];
        yield "string<ASCII>(1)\"␑\"" => ["\x11", "string<ASCII>(1)\"␑\""];
        yield "string<ASCII>(1)\"␒\"" => ["\x12", "string<ASCII>(1)\"␒\""];
        yield "string<ASCII>(1)\"␓\"" => ["\x13", "string<ASCII>(1)\"␓\""];
        yield "string<ASCII>(1)\"␔\"" => ["\x14", "string<ASCII>(1)\"␔\""];
        yield "string<ASCII>(1)\"␕\"" => ["\x15", "string<ASCII>(1)\"␕\""];
        yield "string<ASCII>(1)\"␖\"" => ["\x16", "string<ASCII>(1)\"␖\""];
        yield "string<ASCII>(1)\"␗\"" => ["\x17", "string<ASCII>(1)\"␗\""];
        yield "string<ASCII>(1)\"␘\"" => ["\x18", "string<ASCII>(1)\"␘\""];
        yield "string<ASCII>(1)\"␙\"" => ["\x19", "string<ASCII>(1)\"␙\""];
        yield "string<ASCII>(1)\"␚\"" => ["\x1A", "string<ASCII>(1)\"␚\""];
        yield "string<ASCII>(1)\"␛\"" => ["\x1B", "string<ASCII>(1)\"␛\""];
        yield "string<ASCII>(1)\"␜\"" => ["\x1C", "string<ASCII>(1)\"␜\""];
        yield "string<ASCII>(1)\"␝\"" => ["\x1D", "string<ASCII>(1)\"␝\""];
        yield "string<ASCII>(1)\"␞\"" => ["\x1E", "string<ASCII>(1)\"␞\""];
        yield "string<ASCII>(1)\"␟\"" => ["\x1F", "string<ASCII>(1)\"␟\""];
        yield "string<ASCII>(1)\"␡\"" => ["\x7F", "string<ASCII>(1)\"␡\""];
        yield "array of objects" => [
            [
                (object)[],
                (object)[],
            ],
            'array<integer,object<stdClass>>(2)',
        ];
    }

    public static function provideParseMessage(): \Generator
    {
        yield 'no replace' => [
            'this is the message',
            [
                'a' => 1,
                'b' => 2,
            ],
            'this is the message',
        ];
        yield 'raw replace' => [
            'this is {key.raw} a key and a {unknown.key} unknown key',
            [
                'key' => 'ABC',
            ],
            'this is ABC a key and a {unknown.key} unknown key',
        ];
        yield 'all replacers' => [
            'raw "{key.raw}" type "{key.type}" string "{key.debug}"',
            [
                'key' => 1,
            ],
            'raw "1" type "integer" string "integer 1"',
        ];
        yield 'all replacers array' => [
            'raw "{key.raw}" type "{key.type}" string "{key.debug}"',
            [
                'key' => ['a' => 1, 'f'],
            ],
            'raw "Array" type "array" string "array<mixed,mixed>(2)"',
        ];
        yield 'unknown info' => [
            '{key.blaaa}',
            [
                'key' => 234,
            ],
            '{key.blaaa}',
        ];
        yield 'no recursion' => [
            '{key.raw}',
            [
                'key' => '          {key.raw}',
            ],
            '          {key.raw}',
        ];
        yield 'no modifier is identical to raw' => [
            '{key} {key.raw}',
            [
                'key' => '1',
            ],
            '1 1',
        ];
        yield 'all types' => [
            '{key} | {key.gettype} | {key.type} | {key.debug} | {key.export} | {key.raw}',
            [
                'key' => true,
            ],
            '1 | boolean | true | boolean true | true | 1',
        ];
        yield 'resource raw' => [
            '{key} {key.raw}',
            [
                'key' => fopen('php://memory', 'r'),
            ],
            'resource resource',
        ];
        yield 'object raw' => [
            '{key} {key.raw}',
            [
                'key' => (object)[],
            ],
            'Object Object',
        ];
    }

    /**
     * @param mixed $variable
     * @param string $expected
     *
     * @dataProvider provideTypeDetection
     */
    public function testTypeDetection(mixed $variable, string $expected): void
    {
        self::assertSame($expected, Debug::getType($variable));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideAnything
     */
    public function testTypeDetectionNeverThrowsException(mixed $value): void
    {
        self::assertIsString(Debug::getType($value));
    }

    /**
     * @param $value
     * @param string $expected
     *
     * @dataProvider provideStringify
     */
    public function testStringify($value, string $expected): void
    {
        self::assertSame($expected, Debug::stringify($value));
    }

    public function testStringifyWithRecursiveArray(): void
    {
        $array = null;
        $array = [&$array];
        self::assertSame(
            'array<integer,array>(1)',
            Debug::stringify($array)
        );
    }

    /**
     * @param $value
     *
     * @dataProvider provideAnything
     */
    public function testStringifyNeverThrowsException($value): void
    {
        self::assertIsString(Debug::stringify($value));
    }

    /**
     * @param string $message
     * @param array $parameters
     * @param string $expected
     *
     * @dataProvider provideParseMessage
     */
    public function testParseMessage(string $message, array $parameters, string $expected): void
    {
        self::assertSame(
            $expected,
            Debug::parseMessage(
                $message,
                $parameters
            )
        );
    }

}
