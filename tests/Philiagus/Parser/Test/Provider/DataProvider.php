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

namespace Philiagus\Parser\Test\Provider;

use PHPUnit\Framework\Assert;
use ReflectionObject;

class DataProvider
{

    public const TYPE_ARRAY = 1 << 0;
    public const TYPE_BOOLEAN = 1 << 1;
    public const TYPE_FLOAT = 1 << 2;
    public const TYPE_INTEGER = 1 << 3;
    public const TYPE_OBJECT = 1 << 4;
    public const TYPE_RESOURCE = 1 << 5;
    public const TYPE_STRING = 1 << 6;
    public const TYPE_NULL = 1 << 7;
    public const TYPE_NAN = 1 << 8;
    public const TYPE_INFINITE = 1 << 9;
    public const TYPE_ALL = PHP_INT_MAX;
    public const TYPE_SCALAR = self::TYPE_BOOLEAN | self::TYPE_INTEGER | self::TYPE_FLOAT | self::TYPE_STRING | self::TYPE_NAN | self::TYPE_INFINITE;

    /**
     * @param int $types
     *
     * @param callable $filter
     *
     * @return array
     * @throws \Exception
     */
    public static function provide(int $types, callable $filter = null): array
    {
        $cases = [];

        if ($types & self::TYPE_ARRAY) {
            $cases['array empty'] = [];
            foreach (self::provide($types & ~self::TYPE_ARRAY) as $name => $element) {
                $cases['array of single ' . $name] = $element;
            }
            $cases['array key value'] = ['key' => 'value'];
            $cases['array mixed'] = [1, true, 'string', 1.0];
        }

        if ($types & self::TYPE_BOOLEAN) {
            $cases['boolean true'] = true;
            $cases['boolean false'] = false;
        }

        if ($types & self::TYPE_FLOAT) {
            foreach (
                [
                    PHP_INT_MIN - .5,
                    -666.0,
                    -1.5,
                    -1.0,
                    -.5,
                    -1 / 3,
                    -.3333,
                    -0.0,
                    0.0,
                    .3333,
                    1 / 3,
                    .5,
                    1.0,
                    1.5,
                    M_PI,
                    666.0,
                    PHP_INT_MAX + .5,
                ] as $floatValue
            ) {
                $cases['float ' . $floatValue] = $floatValue;
            }
        }

        if ($types & self::TYPE_INTEGER) {
            $cases['int PHP_INT_MAX'] = PHP_INT_MAX;
            $cases['int PHP_INT_MIN'] = PHP_INT_MIN;
            foreach (
                [
                    -666,
                    -1,
                    0,
                    1,
                    666,
                ] as $integerValue
            ) {
                $cases['int ' . $integerValue] = $integerValue;
            }
        }

        if ($types & self::TYPE_OBJECT) {
            $cases['object stdClass'] = new \stdClass();
            $cases['object \Exception'] = new \Exception();
            $cases['object \DateTime'] = new \DateTime();
        }

        if ($types & self::TYPE_RESOURCE) {
            $cases['resource STDIN'] = STDIN;
        }

        if ($types & self::TYPE_STRING) {
            $cases['string empty'] = '';
            foreach (
                [
                    'hello world',
                    '100',
                    'true',
                    'false',
                    'null',
                ]
                as $stringValue
            ) {
                $cases['string ' . var_export($stringValue, true)] = $stringValue;
            }
        }

        if ($types & self::TYPE_NULL) {
            $cases['null'] = null;
        }

        if ($types & self::TYPE_NAN) {
            $cases['special float NaN'] = NAN;
        }

        if ($types & self::TYPE_INFINITE) {
            $cases['special float INF'] = INF;
            $cases['special float -INF'] = -INF;
        }

        $result = [];
        foreach ($cases as $name => $value) {
            if ($filter && !$filter($value)) {
                continue;
            }
            $result[$name] = [$value];
        }

        return $result;
    }

    /**
     * Checks if the two values are the same but treats NAN === NAN
     *
     * @param mixed $expected
     * @param mixed $value
     *
     * @return bool
     */
    public static function isSame($expected, $value): bool
    {
        try {
            self::assertSame($expected, $value);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Asserts that the two values are the same, treating NAN === NAN
     *
     * @param mixed $expected
     * @param mixed $value
     * @param array $path
     *
     * @throws \ReflectionException
     */
    public static function assertSame($expected, $value, array $path = []): void
    {
        if (count($path) > 100) {
            Assert::fail('Max recursion level for assertion reached');
        }
        Assert::assertSame(gettype($expected), gettype($value));
        switch ($expected instanceof \stdClass ? 'array' : gettype($expected)) {
            case "double":
                if(is_nan($expected) && is_nan($value)) {
                    break;
                }
                Assert::assertSame($expected, $value);
                break;
            case "boolean":
            case "integer":
            case "NULL":
            case "string":
                Assert::assertSame($expected, $value);
                break;
            case "array":
                $expectedArray = (array) $expected;
                $valueArray = (array) $value;
                Assert::assertSame(array_keys($expectedArray), array_keys($valueArray));
                foreach ((array) $expected as $key => $value) {
                    self::assertSame(
                        $value,
                        $valueArray[$key],
                        array_merge($path, [gettype($expected)])
                    );
                }
                break;
            case "object":
                Assert::assertSame(get_class($expected), get_class($value));
                $expectedReflection = new ReflectionObject($expected);
                $valueReflection = new ReflectionObject($value);
                foreach ($expectedReflection->getProperties() as $expectedProperty) {
                    $valueProperty = $valueReflection->getProperty($expectedProperty->getName());
                    $expectedProperty->setAccessible(true);
                    $valueProperty->setAccessible(true);
                    self::assertSame(
                        $expectedProperty->getValue($expected),
                        $valueProperty->getValue($value),
                        array_merge($path, [get_class($expected)])
                    );
                }
                break;
            case 'resource':
                Assert::assertSame(get_resource_type($expected), get_resource_type($value));
                break;
            default:
                Assert::fail('Cannot compare variables of type ' . gettype($expected) . ' at ' . implode(' > ', $path));
                break;
        }
    }

}