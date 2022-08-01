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

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Parser\AssertStdClass;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Subject\PropertyName;
use Philiagus\Parser\Subject\PropertyValue;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTest;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\AssertStdClass
 */
class AssertStdClassTest extends ParserTestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, OverwritableTypeErrorMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        $value = new \stdClass();

        return [
            [$value, fn() => AssertStdClass::new(), $value],
        ];
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => !$value instanceof \stdClass)
            ->map(fn($value) => [$value, fn() => AssertStdClass::new()])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => !$value instanceof \stdClass)
            ->map(fn($value) => [$value, fn() => AssertStdClass::new()])
            ->provide(false);
    }

    public function testGivePropertyValue(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        static fn($value) => array_key_first((array) $value),
                        static fn($value) => !empty((array) $value)
                    )
                    ->error(
                        static fn($value) => implode('|', array_keys((array) $value)) . 'ff'
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value, $generatedArguments) => $value->{$generatedArguments[0]},
                        PropertyValue::class,
                        static fn($value, $generatedArguments) => property_exists($value, $generatedArguments[0])
                    ),
                $builder
                    ->messageArgument()
                    ->expectedWhen(static fn($value, array $generatedArguments, array $successStack) => !$successStack[0] && $successStack[1])
                    ->withParameterElement('property', 0)
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testGiveOptionalPropertyValue(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        static fn($value) => array_key_first((array) $value),
                        static fn($value) => !empty((array) $value)
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value, array $generated) => $value->{$generated[0]},
                        PropertyValue::class,
                    )
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        static fn($value) => implode('|', array_keys((array) $value)) . '|'
                    ),
                $builder
                    ->parserArgument()
                    ->willBeCalledIf(static fn() => false)
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testGiveDefaultedPropertyValue(): void
    {
        $default = new \stdClass();
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        static fn($value) => array_key_first((array) $value),
                        static fn($value) => !empty((array) $value)
                    )
                    ->success(
                        static fn($value) => implode('|', array_keys((array) $value))
                    ),
                $builder
                    ->fixedArgument()
                    ->success($default),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value, $generatedValues) => property_exists($value, $generatedValues[0]) ? $value->{$generatedValues[0]} : $default,
                        PropertyValue::class
                    )
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testGivePropertyNames(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value) => array_map('strval', array_keys((array) $value)),
                        MetaInformation::class
                    )
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testGivePropertyValues(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value) => array_values((array) $value),
                        MetaInformation::class
                    )
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testGiveEachPropertyName(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectMultipleCalls(
                        static fn($value) => array_map('strval', array_keys((array) $value)),
                        PropertyName::class
                    )
                    ->willBeCalledIf(static fn($value) => !empty((array) $value))
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testGiveEachPropertyValue(): void
    {
        $builder = $this->builder();
        $builder->test()->arguments(
            $builder
                ->parserArgument()
                ->expectMultipleCalls(
                    static fn($value) => array_values((array) $value),
                    PropertyValue::class
                )
                ->willBeCalledIf(static fn($value) => !empty((array) $value))
        )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testGivePropertyCount(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value) => count((array) $value),
                        MetaInformation::class
                    )
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testAssertNoSurplusPropertiesExist(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => array_map('strval', array_keys((array) $value)))
                    ->error(fn($value) => [], fn($value) => !empty((array) $value))
                    ->configException(fn($value) => [INF])
                    ->configException(fn($value) => [new \stdClass()]),
                $builder
                    ->messageArgument()
                    ->withGeneratedElements(
                        function ($value, array $arguments) {
                            $properties = array_filter($arguments[0], fn($v) => is_string($v));

                            return array_map(
                                fn($property) => ['property' => (string) $property],
                                array_diff(array_keys((array) $value), $properties)
                            );
                        }
                    )
                    ->expectedWhen(fn($_0, $_1, array $success) => !$success[0])
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [1, 2],
                (object) [],
            ]);
        $builder->run();
    }

    public function testAssertPropertiesExist(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => array_map('strval', array_keys((array) $value)))
                    ->error(fn($value) => [implode('|', array_keys((array) $value)) . '|'])
                    ->configException(fn($value) => [INF])
                    ->configException(fn($value) => [new \stdClass()]),
                $builder
                    ->messageArgument()
                    ->withGeneratedElements(
                        function ($value, array $arguments) {
                            $properties = array_filter($arguments[0], fn($v) => is_string($v));

                            return array_map(
                                fn($property) => ['property' => (string) $property],
                                array_diff($properties, array_keys((array) $value))
                            );
                        }
                    )
                    ->expectedWhen(fn($_0, $_1, array $success) => !$success[0])
            )
            ->values([
                (object) ['a' => 1, 'b' => 2],
                (object) [1, 2],
                (object) [],
            ]);
        $builder->run();
    }


}
