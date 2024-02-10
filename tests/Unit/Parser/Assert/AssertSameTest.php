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
use Philiagus\Parser\Parser\Assert\AssertSame;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\Assert\AssertSame
 */
class AssertSameTest extends TestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->filter(fn($value) => $value === $value)
            ->map(fn($value) => [$value, fn($value) => AssertSame::value($value), $value])
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->map(fn($value) => [$value, fn($value) => AssertSame::value([$value])])
            ->provide(false);
    }
}