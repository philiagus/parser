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
use Philiagus\Parser\Parser\ParseFormEncodedString;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\ParseFormEncodedString
 */
class ParseFormEncodedStringTest extends TestBase
{

    use ChainableParserTest, InvalidValueParserTest, ValidValueParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => ParseFormEncodedString::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return [
            ['a=1&b=2', fn() => ParseFormEncodedString::new(), ['a' => '1', 'b' => '2']],
        ];
    }
}
