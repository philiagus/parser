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
use Philiagus\Parser\Parser\AssertScalar;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\AssertScalar
 */
class AssertScalarTest extends TestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, OverwritableTypeErrorMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_SCALAR))
            ->map(fn($value) => [$value, fn() => AssertScalar::new(), $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_SCALAR))
            ->map(fn($value) => [$value, fn() => AssertScalar::new()])
            ->provide(false);
    }

    public function testStaticCreation(): void
    {
        self::assertInstanceOf(AssertScalar::class, AssertScalar::new());
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_SCALAR))
            ->map(fn($value) => [$value, fn() => AssertScalar::new()])
            ->provide(false);
    }
}
