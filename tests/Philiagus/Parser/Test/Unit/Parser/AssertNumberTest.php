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
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Parser\Parser\AssertNumber;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertNumber
 */
class AssertNumberTest extends TestCase
{


    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, ChainableParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        $parser = AssertNumber::new();
        return (new DataProvider(~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT)))
            ->map(fn($value) => [$value, $parser])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        $parser = AssertNumber::new();
        return (new DataProvider(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT))
            ->map(fn($value) => [$value, $parser, $value])
            ->provide(false);
    }
}
