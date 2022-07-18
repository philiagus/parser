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
use Philiagus\Parser\Parser\AssertArray;
use Philiagus\Parser\Subject\ArrayKey;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\AssertArray
 */
class AssertArrayTest extends ParserTestBase
{

    use ChainableParserTest, InvalidValueParserTest, ValidValueParserTest, SetTypeExceptionMessageTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_ARRAY))
            ->map(static fn($value) => [$value, static fn() => AssertArray::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_ARRAY))
            ->map(static fn($value) => [$value, static fn() => AssertArray::new(), $value])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_ARRAY))
            ->map(static fn($value) => [$value, static fn() => AssertArray::new(), $value])
            ->provide(false);
    }

    public function testGiveEachValue(): void
    {
        $builder = $this->builder();
        $builder->test()->arguments(
            $builder
                ->parserArgument()
                ->expectMultipleCalls(
                    static fn($value) => array_values($value),
                    ArrayValue::class
                )
                ->willBeCalledIf(static fn($value) => !empty($value))
        )
            ->successProvider(DataProvider::TYPE_ARRAY);
        $builder->run();
    }

    public function testGiveEachKey(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectMultipleCalls(
                        static fn($value) => array_keys($value),
                        ArrayKey::class
                    )
                    ->willBeCalledIf(static fn($value) => !empty($value))
            )
            ->successProvider(DataProvider::TYPE_ARRAY);
        $builder->run();
    }

    public function testGiveKeys(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value) => array_keys($value),
                        MetaInformation::class
                    )
            )
            ->successProvider(DataProvider::TYPE_ARRAY);
        $builder->run();
    }

    public function testGiveLength(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value) => count($value),
                        MetaInformation::class
                    )
            )
            ->successProvider(DataProvider::TYPE_ARRAY);
        $builder->run();
    }

    public function testGiveValue(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        static fn($value) => array_key_first($value),
                        static fn($value) => !empty($value)
                    )
                    ->error(
                        static fn($value) => implode('|', array_keys($value)) . 'ff'
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value, $generatedArguments) => $value[$generatedArguments[0]],
                        ArrayValue::class,
                        static fn($value, $generatedArguments) => array_key_exists($generatedArguments[0], $value)
                    ),
                $builder
                    ->messageArgument()
                    ->expectedWhen(static fn($value, array $generatedArguments, array $successStack) => !$successStack[0] && $successStack[1])
                    ->withParameterElement('key', 0)
            )
            ->successProvider(DataProvider::TYPE_ARRAY);
        $builder->run();
    }

    public function testGiveDefaultedKeyValue(): void
    {
        $default = new \stdClass();
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        static fn($value) => array_key_first($value),
                        static fn($value) => !empty($value)
                    )
                    ->success(
                        static fn($value) => implode('|', array_keys($value))
                    ),
                $builder
                    ->fixedArgument()
                    ->success($default),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value, $generatedValues) => array_key_exists($generatedValues[0], $value) ? $value[$generatedValues[0]] : $default,
                        ArrayValue::class
                    )
            )
            ->successProvider(DataProvider::TYPE_ARRAY);
        $builder->run();
    }

    public function testAssertSequentialKeys(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->messageArgument()
                    ->expectedWhen(static fn($value) => !array_is_list($value))
            )
            ->provider(
                DataProvider::TYPE_ARRAY,
                static fn($value) => array_is_list($value)
            );
        $builder->run();
    }

    public function testGiveOptionalKeyValue(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        static fn($value) => array_key_first($value),
                        static fn($value) => !empty($value)
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        static fn($value, array $generated) => $value[$generated[0]],
                        ArrayValue::class,
                    )
            )
            ->successProvider(DataProvider::TYPE_ARRAY);
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        static fn($value) => implode('|', array_keys($value)) . '|'
                    ),
                $builder
                    ->parserArgument()
                    ->willBeCalledIf(static fn() => false)
            )
            ->successProvider(DataProvider::TYPE_ARRAY);
        $builder->run();
    }


}
