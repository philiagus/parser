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
use Philiagus\Parser\Parser\ConvertToInteger;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

/**
 * @covers \Philiagus\Parser\Parser\ConvertToInteger
 */
class ConvertToIntegerTest extends TestBase
{

    use ChainableParserTest, InvalidValueParserTest, ValidValueParserTest, SetTypeExceptionMessageTest;

    public function provideInvalidTypesAndParser(): array
    {
        return $this->provideInvalidValuesAndParsers();
    }

    public function provideInvalidValuesAndParsers(): array
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

    public function provideValidValuesAndParsersAndResults(): array
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
