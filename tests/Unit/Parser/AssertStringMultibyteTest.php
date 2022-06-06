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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertStringMultibyte;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class AssertStringMultibyteTest extends TestBase
{
    private const ISO_8859_1 = "\xE4";
    private const UTF_8 = "\xC3\xBC";
    private const SEVEN_ASCII = 'u';

    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringMultibyte::new(), $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringMultibyte::new()])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringMultibyte::new()])
            ->provide(false);
    }

    public function test_setAvailableEncodings(): void
    {
        AssertStringMultibyte::new()
            ->setAvailableEncodings(['7bit', 'ASCII', 'ISO-8859-1', 'UTF-8'])
            ->giveEncoding($this->prophesizeParser(['ISO-8859-1']))
            ->parse(self::ISO_8859_1);

        AssertStringMultibyte::new()
            ->setAvailableEncodings(['ASCII'])
            ->giveEncoding($this->prophesizeParser(['ASCII']))
            ->parse('abcd');

        AssertStringMultibyte::new()
            ->setAvailableEncodings(['UTF-8'])
            ->giveEncoding($this->prophesizeParser(['UTF-8']))
            ->parse(self::UTF_8);

        self::expectException(ParsingException::class);
        self::expectExceptionMessage('MESSAGE');
        AssertStringMultibyte::new()
            ->setAvailableEncodings(['7bit'], 'MESSAGE')
            ->parse(self::UTF_8);
    }

    public function test_setEncoding(): void
    {
        AssertStringMultibyte::new()
            ->setEncoding('7bit')
            ->parse(self::SEVEN_ASCII);

        AssertStringMultibyte::new()
            ->setEncoding('ISO-8859-1')
            ->parse(self::ISO_8859_1);

        AssertStringMultibyte::new()
            ->setEncoding('UTF-8')
            ->parse(self::UTF_8);

        self::expectException(ParsingException::class);
        self::expectExceptionMessage('MESSAGE');
        AssertStringMultibyte::new()
            ->setEncoding('7bit', 'MESSAGE')
            ->parse(self::UTF_8);
    }

    public function test_giveLength(): void
    {
        AssertStringMultibyte::new()
            ->setEncoding('ISO-8859-1')
            ->giveLength($this->prophesizeParser([2]))
            ->parse(self::UTF_8);

        AssertStringMultibyte::new()
            ->setEncoding('UTF-8')
            ->giveLength($this->prophesizeParser([1]))
            ->parse(self::UTF_8);
    }

    public function test_giveSubstring(): void
    {
        AssertStringMultibyte::new()
            ->setEncoding('UTF-8')
            ->giveSubstring(1, 2, $this->prophesizeParser(['äü']))
            ->parse('öäüüäö');

        AssertStringMultibyte::new()
            ->setEncoding('ISO-8859-1')
            ->giveSubstring(1, 2, $this->prophesizeParser([substr(self::UTF_8 . self::UTF_8, 1, 2)]))
            ->parse(self::UTF_8 . self::UTF_8);

        AssertStringMultibyte::new()
            ->giveSubstring(1, 52, $this->prophesizeParser(['']))
            ->parse('');
    }

    public function test_assertStartsWith(): void
    {
        AssertStringMultibyte::new()
            ->assertStartsWith('üä')
            ->parse('üäü');

        self::expectException(ParsingException::class);
        AssertStringMultibyte::new()
            ->assertStartsWith('äää')
            ->parse('ä');
    }

    public function test_assertEndsWith(): void
    {
        AssertStringMultibyte::new()
            ->assertEndsWith('äü')
            ->parse('üäü');

        self::expectException(ParsingException::class);
        AssertStringMultibyte::new()
            ->assertEndsWith('äää')
            ->parse('ä');
    }

    public function provideSetAvailableEncodingsInvalidEncodings(): array
    {
        return [
            'invalid encoding' => [['UTF-8', 'nope']],
            'not string' => [[true]]
        ];
    }

    /**
     * @param $invalidEncodings
     *
     * @return void
     * @throws ParserConfigurationException
     * @dataProvider provideSetAvailableEncodingsInvalidEncodings
     */
    public function test_setAvailableEncodings_invalidEncoding($invalidEncodings): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertStringMultibyte::new()->setAvailableEncodings($invalidEncodings);
    }

    public function test_setEncoding_invalidEncoding(): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertStringMultibyte::new()->setEncoding('INVALID');
    }
}
