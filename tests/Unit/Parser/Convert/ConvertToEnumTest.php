<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Parser\Convert;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Contract\Result;
use Philiagus\Parser\Contract\Subject;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Parser\Convert\ConvertToEnum;
use Philiagus\Parser\Test\Mock\BackedEnumMock;
use Philiagus\Parser\Test\Mock\UnitEnumMock;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;

/**
 * @covers \Philiagus\Parser\Parser\Convert\ConvertToEnum
 */
class ConvertToEnumTest extends ParserTestBase
{
    use OverwritableTypeErrorMessageTestTrait;

    public function testByName(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->success(UnitEnumMock::class)
            )
            ->call(
                'setNotFoundErrorMessage',
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value) => $value === 'NOPE')
            )
            ->value(UnitEnumMock::VALUE1)
            ->values(
                array_map(
                    fn(\UnitEnum $u) => $u->name,
                    UnitEnumMock::cases()
                ),
                successValidator: function (Subject $subject, Result $result) {
                    $resValue = $result->getValue();
                    $expectedName = $subject->getValue();
                    $expectedValue = null;
                    foreach (UnitEnumMock::cases() as $case) {
                        if ($case->name === $expectedName) {
                            $expectedValue = $case;
                            break;
                        }
                    }
                    if ($resValue !== $expectedValue) {
                        return ['Result does not match'];
                    }

                    return [];
                }
            )
            ->values(
                ['NOPE'],
                fn() => false
            );


        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->success(BackedEnumMock::class)
            )
            ->call(
                'setNotFoundErrorMessage',
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value) => $value === 'NOPE')
            )
            ->value(BackedEnumMock::VALUE1)
            ->values(
                array_map(
                    fn(\UnitEnum $u) => $u->name,
                    BackedEnumMock::cases()
                ),
                successValidator: function (Subject $subject, Result $result) {
                    $resValue = $result->getValue();
                    $expectedName = $subject->getValue();
                    $expectedValue = null;
                    foreach (BackedEnumMock::cases() as $case) {
                        if ($case->name === $expectedName) {
                            $expectedValue = $case;
                            break;
                        }
                    }
                    if ($resValue !== $expectedValue) {
                        return ['Result does not match'];
                    }

                    return [];
                }
            )
            ->values(
                ['NOPE'],
                fn() => false
            );
        $builder->run();
    }

    public function testByValue(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->success(BackedEnumMock::class)
            )
            ->call(
                'setNotFoundErrorMessage',
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value) => $value === 'NOPE')
            )
            ->value(BackedEnumMock::VALUE1)
            ->values(
                array_map(
                    fn(\UnitEnum $u) => $u->value,
                    BackedEnumMock::cases()
                ),
                successValidator: function (Subject $subject, Result $result) {
                    if ($result->getValue() !== BackedEnumMock::tryFrom($subject->getValue())) {
                        return ['Result does not match'];
                    }

                    return [];
                }
            )
            ->values(
                ['NOPE'],
                fn() => false
            );
        $builder->run();
    }

    public function testByValueThenName(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->success(BackedEnumMock::class)
            )
            ->call(
                'setNotFoundErrorMessage',
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value) => $value === 'NOPE')
            )
            ->value(BackedEnumMock::VALUE1)
            ->values(
                array_merge(
                    array_map(fn(\UnitEnum $u) => $u->name, BackedEnumMock::cases()),
                    array_map(fn(\BackedEnum $u) => $u->value, BackedEnumMock::cases())
                ),
                successValidator: function (Subject $subject, Result $result) {
                    $valueByName = null;
                    foreach (BackedEnumMock::cases() as $case) {
                        if ($case->name === $subject->getValue()) {
                            $valueByName = $case;
                            break;
                        }
                    }
                    $expectedValue = BackedEnumMock::tryFrom($subject->getValue()) ?? $valueByName;
                    if ($result->getValue() !== $expectedValue) {
                        return ['Result does not match'];
                    }

                    return [];
                }
            )
            ->values(
                ['NOPE'],
                fn() => false
            );
        $builder->run();
    }

    public function testByNameThenValue(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->success(BackedEnumMock::class)
            )
            ->call(
                'setNotFoundErrorMessage',
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value) => $value === 'NOPE')
            )
            ->value(BackedEnumMock::VALUE1)
            ->values(
                array_merge(
                    array_map(fn(\UnitEnum $u) => $u->name, BackedEnumMock::cases()),
                    array_map(fn(\BackedEnum $u) => $u->value, BackedEnumMock::cases())
                ),
                successValidator: function (Subject $subject, Result $result) {
                    $valueByName = null;
                    foreach (BackedEnumMock::cases() as $case) {
                        if ($case->name === $subject->getValue()) {
                            $valueByName = $case;
                            break;
                        }
                    }
                    $expectedValue = $valueByName ?? BackedEnumMock::tryFrom($subject->getValue());
                    if ($result->getValue() !== $expectedValue) {
                        return ['Result does not match: expected ' . print_r($expectedValue, true) . ' got ' . print_r($result->getValue(), true)];
                    }

                    return [];
                }
            )
            ->values(
                ['NOPE'],
                fn() => false
            );
        $builder->run();
    }


    /**
     * @testWith ["byValue"]
     *           ["byNameThenValue"]
     *           ["byValueThenName"]
     */
    public function testTestForBackedEnum(string $method): void
    {
        self::expectException(ParserConfigurationException::class);
        ConvertToEnum::$method(UnitEnumMock::class);
    }

    /**
     * @testWith ["byName"]
     *           ["byValue"]
     *           ["byNameThenValue"]
     *           ["byValueThenName"]
     */
    public function testTestForEnumClass(string $method): void
    {
        self::expectException(ParserConfigurationException::class);
        ConvertToEnum::$method(\stdClass::class);
    }

    public static function provideInvalidTypesAndParser(): array
    {
        $cases = [];
        foreach ((new DataProvider(~DataProvider::TYPE_STRING & ~DataProvider::TYPE_INTEGER))->provide(false) as $name => $value) {
            $cases["Backed $name"] = [$value, fn() => ConvertToEnum::byValue(BackedEnumMock::class)];
        }
        foreach ((new DataProvider(~DataProvider::TYPE_STRING))->provide(false) as $name => $value) {
            $cases["Unit $name"] = [$value, fn() => ConvertToEnum::byName(BackedEnumMock::class)];
        }

        return $cases;
    }
}
