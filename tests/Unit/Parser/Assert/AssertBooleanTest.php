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
use Philiagus\Parser\Parser\Assert\AssertBoolean;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AssertBoolean::class)]
class AssertBooleanTest extends TestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_BOOLEAN))
            ->map(static fn($value) => [$value, static fn() => AssertBoolean::new(), $value])
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_BOOLEAN))
            ->map(static fn($value) => [$value, static fn() => AssertBoolean::new()])
            ->provide(false);
    }

    public static function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_BOOLEAN))
            ->map(static fn($value) => [$value, static fn() => AssertBoolean::new()])
            ->provide(false);
    }

    public function testStaticCreation(): void
    {
        self::assertInstanceOf(AssertBoolean::class, AssertBoolean::new());
    }
}
