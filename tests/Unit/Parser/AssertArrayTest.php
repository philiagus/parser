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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertArray;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\AssertArray
 */
class AssertArrayTest extends TestBase
{

    use ChainableParserTest, InvalidValueParserTest, ValidValueParserTest, SetTypeExceptionMessageTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_ARRAY))
            ->map(fn($value) => [$value, fn() => AssertArray::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_ARRAY))
            ->map(fn($value) => [$value, fn() => AssertArray::new(), $value])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_ARRAY))
            ->map(fn($value) => [$value, fn() => AssertArray::new(), $value])
            ->provide(false);
    }

    public function testFull(): void
    {
        AssertArray::new()
            ->giveEachValue(
                $this->prophesizeParser([['value 0'],['value key'],['value 1']])
            )
            ->giveEachKey(
                $this->prophesizeParser([[0], ['key'], [1]])
            )
            ->giveKeys($this->prophesizeParser([
                [[0, 'key', 1]]
            ]))
            ->giveKeyValue(0, $this->prophesizeParser([['value 0']]))
            ->giveDefaultedKeyValue(0, 'default', $this->prophesizeParser([['value 0']]))
            ->giveDefaultedKeyValue(7, 'default', $this->prophesizeParser([['default']]))
            ->giveOptionalKeyValue('key', $this->prophesizeParser([['value key']]))
            ->giveOptionalKeyValue('never', $this->prophesizeParser([]))
            ->giveLength($this->prophesizeParser([[3]]))
            ->parse([
                0 => 'value 0',
                'key' => 'value key',
                1 => 'value 1'
            ]);
    }

    public function testAssertSequentialKeys(): void
    {
        AssertArray::new()
            ->assertSequentialKeys()
            ->parse([1,2,3,4]);

        self::expectException(ParsingException::class);
        AssertArray::new()
            ->assertSequentialKeys()
            ->parse(
                [1,2,3,4, 'yes' => 'no', 5,4,3,2]
            );
    }
}
