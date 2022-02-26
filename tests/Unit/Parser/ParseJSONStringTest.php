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
use Philiagus\Parser\Parser\ParseJSONString;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class ParseJSONStringTest extends TestCase
{

    use ChainableParserTest, InvalidValueParserTest, ValidValueParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->addCase('not json string', '?=)(/&%')
            ->map(fn($value) => [$value, fn() => ParseJSONString::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        $parser = ParseJSONString::new();

        return (new DataProvider(DataProvider::TYPE_STRING))
            ->filter(function ($value) {
                @json_encode($value);

                return json_last_error() === JSON_ERROR_NONE;
            })
            ->map(fn($value) => [json_encode($value), fn() => ParseJSONString::new(), $value])
            ->provide(false);
    }

}
