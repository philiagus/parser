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
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Parser\Assert\AssertObject;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\Assert\AssertObject
 */
class AssertObjectTest extends ParserTestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;


    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_OBJECT))
            ->map(fn($value) => [$value, fn() => AssertObject::new()])
            ->provide(false);
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_OBJECT))
            ->map(fn($value) => [$value, fn() => AssertObject::new(), $value])
            ->provide(false);
    }

    public static function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_OBJECT))
            ->map(fn($value) => [$value, fn() => AssertObject::new()])
            ->provide(false);
    }

    public function testInstanceOf(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(static fn($value) => get_class($value))
                    ->error(static fn($value) => $value instanceof \stdClass ? Parser::class : \stdClass::class),
                $builder
                    ->messageArgument()
                    ->withParameterElement('class', 0)
                    ->expectedWhen(static fn($value, array $args) => !$value instanceof $args[0])
            )
            ->successProvider(DataProvider::TYPE_OBJECT);
        $builder->run();
    }

    public function testAssertInstanceOf(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(static fn($value) => get_class($value))
                    ->error(static fn($value) => $value instanceof \stdClass ? Parser::class : \stdClass::class),
                $builder
                    ->messageArgument()
                    ->withParameterElement('class', 0)
                    ->expectedWhen(static fn($value, array $args) => !$value instanceof $args[0])
            )
            ->successProvider(DataProvider::TYPE_OBJECT);
        $builder->run();
    }

    public function testAssertNotInstanceOf(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->error(static fn($value) => get_class($value))
                    ->success(static fn($value) => $value instanceof \stdClass ? Parser::class : \stdClass::class),
                $builder
                    ->messageArgument()
                    ->withParameterElement('class', 0)
                    ->expectedWhen(static fn($value, array $args) => $value instanceof $args[0])
            )
            ->successProvider(DataProvider::TYPE_OBJECT);
        $builder->run();
    }
}
