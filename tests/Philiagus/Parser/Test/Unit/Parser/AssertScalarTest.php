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
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertScalar
 */
class AssertScalarTest extends TestCase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_SCALAR))
            ->map(fn($value) => [$value, AssertScalar::new(), $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_SCALAR))
            ->map(fn($value) => [$value, AssertScalar::new()])
            ->provide(false);
    }

    public function testStaticCreation(): void
    {
        self::assertInstanceOf(AssertScalar::class, AssertScalar::new());
    }
}
