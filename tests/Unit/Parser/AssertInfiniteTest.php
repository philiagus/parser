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
use Philiagus\Parser\Parser\AssertInfinite;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\AssertInfinite
 */
class AssertInfiniteTest extends ParserTestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INFINITE))
            ->map(static fn($value) => [$value, static fn() => AssertInfinite::new(), $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INFINITE))
            ->map(static fn($value) => [$value, static fn() => AssertInfinite::new()])
            ->provide(false);
    }

    public function testSetAssertSignToPositive(): void
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

    public function testSetAssertSignToNegative(): void
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
