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
use Philiagus\Parser\Parser\Assert\AssertFloat;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AssertFloat::class)]
class AssertFloatTest extends NumberTestBase
{

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_FLOAT))
            ->map(static fn($value) => [$value, static fn() => AssertFloat::new()])
            ->provide(false);
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_FLOAT | DataProvider::TYPE_INFINITE))
            ->map(static fn($value) => [$value, static fn() => AssertFloat::new()->setAllowInfinite(is_infinite($value)), $value])
            ->provide(false);
    }

    protected static function getSuccessDataProviderUnion(): int
    {
        return DataProvider::TYPE_FLOAT;
    }
}
