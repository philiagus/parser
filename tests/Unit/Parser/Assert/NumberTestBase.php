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

namespace Philiagus\Parser\Test\Unit\Parser\Assert;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

abstract class NumberTestBase extends ParserTestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public static function provideInvalidTypesAndParser(): array
    {
        $class = static::getCoveredClass();
        $filter = static::getSuccessDataProviderUnion();
        return (new DataProvider(~$filter))
            ->map(static fn($value) => [$value, static fn() => $class::new()])
            ->provide(false);
    }

    abstract protected static function getSuccessDataProviderUnion(): int;

    public function testAssertRange(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value)
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN),
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value)
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0] || $value > $args[1])
                    ->withParameterElement('min', 0)
                    ->withParameterElement('max', 1)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testRange(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value)
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN),
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value)
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0] || $value > $args[1])
                    ->withParameterElement('min', 0)
                    ->withParameterElement('max', 1)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testAssertMinimum(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value)
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0])
                    ->withParameterElement('min', 0)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testMinimum(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value)
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0])
                    ->withParameterElement('min', 0)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testAssertMaximum(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value)
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value > $args[0])
                    ->withParameterElement('max', 0)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testMaximum(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value)
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value > $args[0])
                    ->withParameterElement('max', 0)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }


    public function testAssertLowerThan(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value > $args[0])
                    ->withParameterElement('upper', 0)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testLowerThan(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value > $args[0])
                    ->withParameterElement('upper', 0)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testAssertGreaterThan(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0])
                    ->withParameterElement('lower', 0)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testGreaterThan(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0])
                    ->withParameterElement('lower', 0)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testAssertBetween(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN),
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0] || $value > $args[1])
                    ->withParameterElement('lower', 0)
                    ->withParameterElement('upper', 1)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }

    public function testBetween(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN),
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0] || $value > $args[1])
                    ->withParameterElement('lower', 0)
                    ->withParameterElement('upper', 1)
            )
            ->successProvider(static::getSuccessDataProviderUnion());
        $builder->run();
    }
}
