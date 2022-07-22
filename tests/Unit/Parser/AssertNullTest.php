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
use Philiagus\Parser\Parser\AssertNull;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\AssertNull
 */
class AssertNullTest extends TestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_NULL))
            ->map(function ($value) {
                return [$value, static fn() => AssertNull::new(), $value];
            })
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_NULL))
            ->map(function ($value) {
                return [$value, static fn() => AssertNull::new()];
            })
            ->provide(false);
    }

    public function testStaticConstructor(): void
    {
        self::assertInstanceOf(AssertNull::class, AssertNull::new());
    }
}
