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
use Philiagus\Parser\Parser\AssertString;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class AssertStringTest extends TestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertString::new(), $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertString::new()])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertString::new()])
            ->provide(false);
    }

    public function test_giveLength(): void
    {
        $parser = AssertString::new()
            ->giveLength($this->prophesizeParser([0, 10]));
        $parser->parse('');
        $parser->parse('0123456789');
    }

    public function test_giveSubstring(): void
    {
        AssertString::new()
            ->giveSubstring(0, 3, $this->prophesizeParser(['012']))
            ->giveSubstring(9, 10, $this->prophesizeParser(['9']))
            ->parse('0123456789');

        AssertString::new()
            ->giveSubstring(10, 1000, $this->prophesizeParser(['']))
            ->parse('');
    }

    public function test_assertStartsWith(): void
    {
        $parser = AssertString::new()->assertStartsWith('0123');
        $parser->parse('0123456789');

        self::expectException(ParsingException::class);
        $parser->parse('abcdef');
    }

    public function test_assertEndsWith(): void
    {
        $parser = AssertString::new()->assertEndsWith('789');
        $parser->parse('0123456789');

        self::expectException(ParsingException::class);
        $parser->parse('abcdef');
    }




}
