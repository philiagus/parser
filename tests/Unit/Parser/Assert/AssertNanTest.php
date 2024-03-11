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
use Philiagus\Parser\Parser\Assert\AssertNan;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AssertNan::class)]
class AssertNanTest extends TestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_NAN))
            ->map(function ($value) {
                return [$value, static fn() => AssertNan::new(), $value];
            })
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_NAN))
            ->map(function ($value) {
                return [$value, static fn() => AssertNan::new()];
            })
            ->provide(false);
    }

    public function testStaticConstructor(): void
    {
        self::assertInstanceOf(AssertNan::class, AssertNan::new());
    }
}
