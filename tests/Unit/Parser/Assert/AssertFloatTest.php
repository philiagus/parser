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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Parser\Assert\AssertFloat;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AssertFloat::class)]
class AssertFloatTest extends ParserTestBase
{

    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, ChainableParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_FLOAT))
            ->map(static fn($value) => [$value, static fn() => AssertFloat::new()])
            ->provide(false);
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_FLOAT))
            ->map(static fn($value) => [$value, static fn() => AssertFloat::new(), $value])
            ->provide(false);
    }

    public static function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_FLOAT))
            ->map(static fn($value) => [$value, static fn() => AssertFloat::new()])
            ->provide(false);
    }

    public function testAssertRange(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN)
                    ->configException(fn() => INF)
                    ->configException(fn() => -INF),
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN)
                    ->configException(fn() => INF)
                    ->configException(fn() => -INF),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0] || $value > $args[1])
                    ->withParameterElement('min', 0)
                    ->withParameterElement('max', 1)
            )
            ->successProvider(DataProvider::TYPE_FLOAT);
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
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1)
                    ->configException(fn() => NAN)
                    ->configException(fn() => INF)
                    ->configException(fn() => -INF),
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1)
                    ->configException(fn() => NAN)
                    ->configException(fn() => INF)
                    ->configException(fn() => -INF),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0] || $value > $args[1])
                    ->withParameterElement('min', 0)
                    ->withParameterElement('max', 1)
            )
            ->successProvider(DataProvider::TYPE_FLOAT);
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
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0])
                    ->withParameterElement('min', 0)
            )
            ->successProvider(DataProvider::TYPE_FLOAT);
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
                    ->success(fn($value) => $value - abs($value) - 1)
                    ->error(fn($value) => $value + abs($value) + 1),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value < $args[0])
                    ->withParameterElement('min', 0)
            )
            ->successProvider(DataProvider::TYPE_FLOAT);
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
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value > $args[0])
                    ->withParameterElement('max', 0)
            )
            ->successProvider(DataProvider::TYPE_FLOAT);
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
                    ->success(fn($value) => $value + abs($value) + 1)
                    ->error(fn($value) => $value - abs($value) - 1),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args) => $value > $args[0])
                    ->withParameterElement('max', 0)
            )
            ->successProvider(DataProvider::TYPE_FLOAT);
        $builder->run();
    }

    public static function provideInvalidFloats(): array
    {
        return (new DataProvider(DataProvider::TYPE_NAN | DataProvider::TYPE_INFINITE))
            ->provide();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvalidFloats')]
    public function testAssertMinimumInvalidArgument($value): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertFloat::new()->assertMinimum($value);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvalidFloats')]
    public function testAssertMaximumInvalidArgument($value): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertFloat::new()->assertMaximum($value);
    }

}
