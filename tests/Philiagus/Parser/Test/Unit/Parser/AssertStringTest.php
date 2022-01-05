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
use Philiagus\Parser\Parser\AssertString;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class AssertStringTest extends TestCase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        $parser = AssertString::new();
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, $parser, $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        $parser = AssertString::new();
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, $parser])
            ->provide(false);
    }
}
