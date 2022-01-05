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
use Philiagus\Parser\Parser\AssertArray;
use Philiagus\Parser\Parser\ParseBase64String;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class ParseBase64StringTest extends TestCase
{
    use ChainableParserTest, InvalidValueParserTest, ValidValueParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        $parser = ParseBase64String::new();
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->addCase('not base 64 string', '?=)(/&%')
            ->map(fn($value) => [$value, $parser])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        $parser = ParseBase64String::new();
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [base64_encode($value), $parser, $value])
            ->provide(false);
    }
}
