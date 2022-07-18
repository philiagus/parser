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
use Philiagus\Parser\Parser\AssertSame;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertSame
 */
class AssertSameTest extends TestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->filter(fn($value) => $value === $value)
            ->map(fn($value) => [$value, fn($value) => AssertSame::value($value), $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->map(fn($value) => [$value, fn($value) => AssertSame::value([$value])])
            ->provide(false);
    }
}
