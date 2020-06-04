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

namespace Philiagus\Parser\Test\Util;

use Philiagus\Parser\Test\Provider\DataProvider;
use Philiagus\Parser\Util\Debug;
use PHPUnit\Framework\TestCase;

class DebugTest extends TestCase
{
    public function provideAnything(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    public function provideTypeDetection(): array
    {
        $cases = [
            'true' => [true, 'boolean'],
            'false' => [false, 'boolean'],
            'NAN' => [NAN, 'NAN'],
            'INF' => [INF, 'INF'],
            '-INF' => [-INF, '-INF'],
            'integer' => [100, 'integer'],
            'NULL' => [null, 'NULL'],
            'resource' => [fopen('php://memory', 'r'), 'resource'],
            'array' => [['array'], 'array'],
        ];

        foreach (
            [
                new \stdClass(),
                new \Exception(),
                new \DateTime(),
            ] as $object
        ) {
            $class = get_class($object);
            $cases["object $class"] = [$object, "object<$class>"];
        }

        return $cases;
    }

    /**
     * @param $variable
     * @param $expected
     *
     * @dataProvider provideTypeDetection
     */
    public function testTypeDetection($variable, string $expected): void
    {
        self::assertSame($expected, Debug::getType($variable));
    }

    /**
     * @param $value
     *
     * @dataProvider provideAnything
     */
    public function testTypeDetectionNeverThrowsException($value): void
    {
        self::assertIsString(Debug::getType($value));
    }

    public function provideStringify(): array
    {
        $cases = [
            'integer ' . PHP_INT_MAX => [PHP_INT_MAX, 'integer ' . PHP_INT_MAX],
            'integer 10' => [10, 'integer 10'],
            'integer 0' => [0, 'integer 0'],
            'integer -10' => [-10, 'integer -10'],
            'integer ' . PHP_INT_MIN => [PHP_INT_MIN, 'integer ' . PHP_INT_MIN],
            'float -99.9' => [-99.9, 'float -99.9'],
            'float -0' => [-0.0, 'float -0'],
            'float 0' => [0.0, 'float 0'],
            'float 99.498' => [99.498, 'float 99.498'],
            'float INF' => [INF, 'INF'],
            'float -INF' => [-INF, '-INF'],
            'float NAN' => [NAN, 'NAN'],
            'boolean TRUE' => [true, 'boolean TRUE'],
            'boolean FALSE' => [false, 'boolean FALSE'],
            'NULL' => [null, 'NULL'],
            'resource' => [fopen('php://memory', 'r'), 'resource'],
            'array(0)' => [[], 'array(0)'],
            'object<stdClass>' => [(object) [], 'object<stdClass>'],
            'string<ASCII>(15)"012345678901234"' => ['012345678901234', 'string<ASCII>(15)"012345678901234"'],
            'string<UTF-8>(2)"ü"' => ['ü', 'string<UTF-8>(2)"ü"'],
            'string<binary>(1)"' . chr(252) . '"' => [chr(252), 'string<binary>(1)"' . chr(252) . '"'],
            "string<ASCII>(100)\"0000000000000000000000000000000\u{2026}\"" => [str_repeat('0', 100), "string<ASCII>(100)\"0000000000000000000000000000000\u{2026}\""],
            "string<ASCII>(10)\"          \"" => ['          ', "string<ASCII>(10)\"          \""],
            "string<ASCII>(1)\"␀\"" => ["\x00", "string<ASCII>(1)\"␀\""],
            "string<UTF-8>(1)\"␁\"" => ["\x01", "string<UTF-8>(1)\"␁\""],
            "string<UTF-8>(1)\"␂\"" => ["\x02", "string<UTF-8>(1)\"␂\""],
            "string<UTF-8>(1)\"␃\"" => ["\x03", "string<UTF-8>(1)\"␃\""],
            "string<UTF-8>(1)\"␄\"" => ["\x04", "string<UTF-8>(1)\"␄\""],
            "string<UTF-8>(1)\"␅\"" => ["\x05", "string<UTF-8>(1)\"␅\""],
            "string<UTF-8>(1)\"␆\"" => ["\x06", "string<UTF-8>(1)\"␆\""],
            "string<UTF-8>(1)\"␇\"" => ["\x07", "string<UTF-8>(1)\"␇\""],
            "string<UTF-8>(1)\"␈\"" => ["\x08", "string<UTF-8>(1)\"␈\""],
            "string<ASCII>(1)\"␉\"" => ["\x09", "string<ASCII>(1)\"␉\""],
            "string<ASCII>(1)\"␊\"" => ["\x0A", "string<ASCII>(1)\"␊\""],
            "string<UTF-8>(1)\"␋\"" => ["\x0B", "string<UTF-8>(1)\"␋\""],
            "string<UTF-8>(1)\"␌\"" => ["\x0C", "string<UTF-8>(1)\"␌\""],
            "string<ASCII>(1)\"␍\"" => ["\x0D", "string<ASCII>(1)\"␍\""],
            "string<UTF-8>(1)\"␎\"" => ["\x0E", "string<UTF-8>(1)\"␎\""],
            "string<UTF-8>(1)\"␏\"" => ["\x0F", "string<UTF-8>(1)\"␏\""],
            "string<UTF-8>(1)\"␐\"" => ["\x10", "string<UTF-8>(1)\"␐\""],
            "string<UTF-8>(1)\"␑\"" => ["\x11", "string<UTF-8>(1)\"␑\""],
            "string<UTF-8>(1)\"␒\"" => ["\x12", "string<UTF-8>(1)\"␒\""],
            "string<UTF-8>(1)\"␓\"" => ["\x13", "string<UTF-8>(1)\"␓\""],
            "string<UTF-8>(1)\"␔\"" => ["\x14", "string<UTF-8>(1)\"␔\""],
            "string<UTF-8>(1)\"␕\"" => ["\x15", "string<UTF-8>(1)\"␕\""],
            "string<UTF-8>(1)\"␖\"" => ["\x16", "string<UTF-8>(1)\"␖\""],
            "string<UTF-8>(1)\"␗\"" => ["\x17", "string<UTF-8>(1)\"␗\""],
            "string<UTF-8>(1)\"␘\"" => ["\x18", "string<UTF-8>(1)\"␘\""],
            "string<UTF-8>(1)\"␙\"" => ["\x19", "string<UTF-8>(1)\"␙\""],
            "string<UTF-8>(1)\"␚\"" => ["\x1A", "string<UTF-8>(1)\"␚\""],
            "string<UTF-8>(1)\"␛\"" => ["\x1B", "string<UTF-8>(1)\"␛\""],
            "string<UTF-8>(1)\"␜\"" => ["\x1C", "string<UTF-8>(1)\"␜\""],
            "string<UTF-8>(1)\"␝\"" => ["\x1D", "string<UTF-8>(1)\"␝\""],
            "string<UTF-8>(1)\"␞\"" => ["\x1E", "string<UTF-8>(1)\"␞\""],
            "string<UTF-8>(1)\"␟\"" => ["\x1F", "string<UTF-8>(1)\"␟\""],
            "string<ASCII>(1)\"␡\"" => ["\x7F", "string<ASCII>(1)\"␡\""],
            "array of objects" => [
                [
                    (object) [],
                    (object) [],
                ],
                'array<integer,object<stdClass>>(2)',
            ],
        ];

        return $cases;
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

    public function provideParseMessage(): array
    {
        return [
            'no replace' => [
                'this is the message',
                [
                    'a' => 1,
                    'b' => 2,
                ],
                'this is the message',
            ],
            'raw replace' => [
                'this is {key.raw} a key and a {unknown.key} unknown key',
                [
                    'key' => 'ABC',
                ],
                'this is ABC a key and a {unknown.key} unknown key',
            ],
            'all replacers' => [
                'raw "{key.raw}" type "{key.type}" string "{key.debug}"',
                [
                    'key' => 1,
                ],
                'raw "1" type "integer" string "integer 1"',
            ],
            'all replacers array' => [
                'raw "{key.raw}" type "{key.type}" string "{key.debug}"',
                [
                    'key' => ['a' => 1, 'f'],
                ],
                'raw "Array" type "array" string "array<mixed,mixed>(2)"',
            ],
            'unknown info' => [
                '{key.blaaa}',
                [
                    'key' => 234,
                ],
                '{key.blaaa}',
            ],
            'no recursion' => [
                '{key.raw}',
                [
                    'key' => '          {key.raw}',
                ],
                '          {key.raw}',
            ],
            'no modifier is identical to raw' => [
                '{key} {key.raw}',
                [
                    'key' => '1',
                ],
                '1 1',
            ],
            'all types' => [
                '{key} {key.gettype} {key.type} {key.debug} {key.export} {key.raw}',
                [
                    'key' => true,
                ],
                '1 boolean boolean boolean TRUE true 1',
            ],
            'resource raw' => [
                '{key} {key.raw}',
                [
                    'key' => fopen('php://memory', 'r')
                ],
                'resource resource'
            ]
        ];
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
