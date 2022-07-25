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
use Philiagus\Parser\Parser\AssertBoolean;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\AssertBoolean
 */
class AssertBooleanTest extends TestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, OverwritableTypeErrorMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_BOOLEAN))
            ->map(static fn($value) => [$value, static fn() => AssertBoolean::new(), $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_BOOLEAN))
            ->map(static fn($value) => [$value, static fn() => AssertBoolean::new()])
            ->provide(false);
    }

    public function testStaticCreation(): void
    {
        self::assertInstanceOf(AssertBoolean::class, AssertBoolean::new());
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_BOOLEAN))
            ->map(static fn($value) => [$value, static fn() => AssertBoolean::new()])
            ->provide(false);
    }
}
