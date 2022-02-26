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
use Philiagus\Parser\Parser\Any;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\Any
 */
class AnyTest extends TestCase
{
    use ChainableParserTest, ValidValueParserTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(fn($value) => [$value, fn() => Any::new(), $value])
            ->provide(false);
    }

    public function testStaticCreation(): void
    {
        self::assertInstanceOf(Any::class, Any::new());
    }
}