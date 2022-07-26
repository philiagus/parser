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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\ParseArray;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\ArrayKey;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\ParseArray
 */
class ParseArrayTest extends ParserTestBase
{

    use ChainableParserTest, InvalidValueParserTest, ValidValueParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_ARRAY))
            ->map(fn($value) => [$value, fn() => ParseArray::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_ARRAY))
            ->map(fn($value) => [$value, fn() => ParseArray::new(), $value])
            ->provide(false);
    }

    public function testModifyEachValue(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectMultipleCalls(
                        fn($value) => array_values($value),
                        ArrayValue::class,
                        result: fn(\Philiagus\Parser\Contract\Subject $subject) => new Result($subject, $subject->getValue() . 'f', [])
                    )
            )
            ->values(
                [
                    ['a' => 123, 'b' => 123, 'c' => 632],
                ],
                successValidator: function (\Philiagus\Parser\Contract\Subject $start, \Philiagus\Parser\Contract\Result $result): array {
                    $expected = array_map(fn($value) => $value . 'f', $start->getValue());
                    $received = $result->getValue();
                    if ($expected != $received) {
                        return ['Parser changes have not been correctly applied'];
                    }

                    return [];
                }
            );
        $builder->run();
    }

    public function testDefaultKey(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        fn(array $value) => array_key_first($value),
                        fn(array $value) => !empty($value)
                    )
                    ->success(
                        fn(array $value) => implode('|', array_keys($value)) . '|'
                    ),
                $builder
                    ->dataProviderArgument()
            )
            ->provider(
                DataProvider::TYPE_ARRAY,
                successValidator: static function (\Philiagus\Parser\Contract\Subject $subject, \Philiagus\Parser\Contract\Result $result, array $args): array {
                    $expected = $subject->getValue();
                    if (!array_key_exists($args[0], $expected)) {
                        $expected[$args[0]] = $args[1];
                    }
                    if (!DataProvider::isSame($expected, $result->getValue())) {
                        return ['Result does not match expected result'];
                    }

                    return [];
                }
            );
        $builder->run();
    }

    public function testUnionWith(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->dataProviderArgument(DataProvider::TYPE_ARRAY)
            )
            ->provider(
                DataProvider::TYPE_ARRAY,
                successValidator: static function (\Philiagus\Parser\Contract\Subject $subject, \Philiagus\Parser\Contract\Result $result, array $args): array {
                    $expected = $subject->getValue() + $args[0];
                    if (!DataProvider::isSame($expected, $result->getValue())) {
                        return ['Result does not match expected result'];
                    }

                    return [];
                }
            );
        $builder->run();
    }

    public function testForceSequentialKeys(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->provider(
                DataProvider::TYPE_ARRAY,
                successValidator: static function (\Philiagus\Parser\Contract\Subject $subject, \Philiagus\Parser\Contract\Result $result): array {
                    $expected = array_values($subject->getValue());
                    if (!DataProvider::isSame($expected, $result->getValue())) {
                        return ['Result does not match expected result'];
                    }

                    return [];
                }
            );
        $builder->run();
    }

    public function testModifyEachKey(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectMultipleCalls(
                        fn($value) => array_keys($value),
                        ArrayKey::class,
                        result: fn(\Philiagus\Parser\Contract\Subject $subject) => new Result($subject, $subject->getValue() . 'f', [])
                    )
            )
            ->values(
                [
                    ['a' => 123, 'b' => 123, 'c' => 632],
                ],
                successValidator: function (\Philiagus\Parser\Contract\Subject $start, \Philiagus\Parser\Contract\Result $result): array {
                    $expected = [];
                    foreach ($start->getValue() as $name => $value) {
                        $expected[$name . 'f'] = $value;
                    }
                    $received = $result->getValue();
                    if ($expected !== $received) {
                        return ['Parser changes have not been correctly applied'];
                    }

                    return [];
                }
            );

        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectMultipleCalls(
                        fn($value) => array_keys($value),
                        ArrayKey::class,
                        result: fn(\Philiagus\Parser\Contract\Subject $subject) => new Result($subject, null, [])
                    )
                    ->willBeCalledIf(fn($value) => !empty($value)),
                $builder
                    ->messageArgument()
                    ->withGeneratedElements(
                        function ($value) {
                            return array_map(
                                fn($key) => ['oldKey' => $key, 'newKey' => null],
                                array_keys($value)
                            );
                        }
                    )
                    ->expectedWhen(fn($value, $_, array $successes) => !empty($value) && $successes[0])
            )
            ->provider(
                DataProvider::TYPE_ARRAY,
                fn($value) => empty($value)
            );
        $builder->run();
    }


    public function testModifyOptionalValue(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        fn($value) => array_key_first($value),
                        fn($value) => !empty($value)
                    )
                    ->success(
                        fn($value) => implode('|', array_keys($value)) . '|'
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value, array $generatedValues) => $value[$generatedValues[0]],
                        ArrayValue::class,
                        result: fn(\Philiagus\Parser\Contract\Subject $subject) => new Result($subject, $subject->getValue() . 'f', [])
                    )
                    ->willBeCalledIf(
                        fn($value, array $generatedValues) => array_key_exists($generatedValues[0], $value)
                    )
            )
            ->values(
                [
                    ['a' => 123, 'b' => 234, 'c' => 345],
                    [],
                ],
                successValidator: function (\Philiagus\Parser\Contract\Subject $subject, \Philiagus\Parser\Contract\Result $result, array $methodArgs): array {
                    $expectedResult = $subject->getValue();
                    if (array_key_exists($methodArgs[0], $expectedResult)) {
                        $expectedResult[$methodArgs[0]] .= 'f';
                    }
                    if ($result->getValue() !== $expectedResult) {
                        return ['Value was not altered as expected'];
                    }

                    return [];
                }
            );
        $builder->run();
    }


    public function testModifyValue(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(
                        fn($value) => array_key_first($value),
                        fn($value) => !empty($value)
                    )
                    ->error(
                        fn($value) => implode('|', array_keys($value)) . '|'
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value, array $generatedValues) => $value[$generatedValues[0]],
                        ArrayValue::class,
                        result: fn(\Philiagus\Parser\Contract\Subject $subject) => new Result($subject, $subject->getValue() . 'f', [])
                    )
                    ->willBeCalledIf(
                        fn($value, array $generatedValues) => array_key_exists($generatedValues[0], $value)
                    ),
                $builder
                    ->messageArgument()
                    ->withParameterElement('key', 0)
                    ->expectedWhen(
                        fn($value, array $generatedValues) => !array_key_exists($generatedValues[0], $value)
                    )
            )
            ->values(
                [
                    ['a' => 123, 'b' => 234, 'c' => 345],
                    [],
                ],
                successValidator: function (\Philiagus\Parser\Contract\Subject $subject, \Philiagus\Parser\Contract\Result $result, array $methodArgs): array {
                    $expectedResult = $subject->getValue();
                    $expectedResult[$methodArgs[0]] .= 'f';
                    if ($result->getValue() !== $expectedResult) {
                        return ['Value was not altered as expected'];
                    }

                    return [];
                }
            );
        $builder->run();
    }
}
