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
use Philiagus\Parser\Parser\Assert\AssertInfinite;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\Assert\AssertInfinite
 */
class AssertInfiniteTest extends ParserTestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INFINITE))
            ->map(static fn($value) => [$value, static fn() => AssertInfinite::new(), $value])
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INFINITE))
            ->map(static fn($value) => [$value, static fn() => AssertInfinite::new()])
            ->provide(false);
    }

    public function testSetAssertPositive(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value) => $value < 0)
            )
            ->provider(
                DataProvider::TYPE_INFINITE,
                fn($value) => $value > 0
            );
        $builder->run();
    }

    public function testSetAssertNegative(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value) => $value > 0)
            )
            ->provider(
                DataProvider::TYPE_INFINITE,
                fn($value) => $value < 0
            );
        $builder->run();
    }
}
