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
use Philiagus\Parser\Parser\ParseStdClass;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class ParseStdClassTest extends TestCase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        $value = new \stdClass();

        return [
            [$value, fn() => ParseStdClass::new(), $value],
        ];
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => !$value instanceof \stdClass)
            ->map(fn($value) => [$value, fn() => ParseStdClass::new()])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => !$value instanceof \stdClass)
            ->map(fn($value) => [$value, fn() => ParseStdClass::new()])
            ->provide(false);
    }
}
