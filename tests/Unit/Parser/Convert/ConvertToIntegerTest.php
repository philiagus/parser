<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Parser\Convert;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Parser\Convert\ConvertToInteger;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConvertToInteger::class)]
class ConvertToIntegerTest extends TestBase
{

    use ChainableParserTestTrait, InvalidValueParserTestTrait, ValidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public static function provideInvalidTypesAndParser(): array
    {
        return self::provideInvalidValuesAndParsers();
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INTEGER & ~DataProvider::TYPE_FLOAT & ~DataProvider::TYPE_STRING))
            ->filter(
                fn($value) => !is_numeric($value) || $value != (int) $value
            )
            ->addCase('float', 1.2)
            ->addCase('float string', '1.2')
            ->addCase('> integer', PHP_INT_MAX + PHP_INT_MAX)
            ->addCase('< integer', PHP_INT_MIN + PHP_INT_MIN)
            ->map(
                fn($value) => [$value, fn() => ConvertToInteger::new()]
            )
            ->provide(false);
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INTEGER))
            ->addCase('string', '001')
            ->addCase('float -0.0', -0.0)
            ->addCase('float 1.0', 1.0)
            ->addCase('float -23.0', -23.0)
            ->addCase('string 0', '0000')
            ->map(
                fn($value) => [$value, fn() => ConvertToInteger::new(), (int) $value]
            )
            ->provide(false);
    }
}
