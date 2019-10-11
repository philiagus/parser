<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Philiagus\Test\Parser\Unit\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertIntegerTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertInteger()) instanceof Parser);
    }

    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int)~DataProvider::TYPE_INTEGER);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testThatNonIntegersAreBlocked($value): void
    {
        self::expectException(ParsingException::class);
        (new AssertInteger())->parse($value);
    }

    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_INTEGER);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideValidValues
     */
    public function testThatIntegersAreAllowed($value): void
    {
        $result = (new AssertInteger())->parse($value);
        self::assertSame($result, $value);
    }

    public function testThatMinimumCannotBeGreaterThanMaximum(): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertInteger())->withMaximum(100)->withMinimum(1000);
    }

    public function testThatMaximumCannotBeLowerThanMinimum(): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertInteger())->withMinimum(1000)->withMaximum(100);
    }

    public function testThatValueMustBeGreaterThanMinimum(): void
    {
        self::expectException(ParsingException::class);
        (new AssertInteger())
            ->withMinimum(10)
            ->parse(0);
    }

    public function testThatValueMustBeLowerThanMaximum(): void
    {
        self::expectException(ParsingException::class);
        (new AssertInteger())
            ->withMaximum(0)
            ->parse(10);
    }

    public function provideOutOfRangeValues(): array
    {
        return [
            'lower' => [-1],
            'upper' => [11],
        ];
    }

    public function testThatValueOverMinimumPasses(): void
    {
        $parser = (new AssertInteger())->withMinimum(0);
        self::assertSame(10, $parser->parse(10));
        self::assertSame(0, $parser->parse(0));
        self::assertSame(PHP_INT_MAX, $parser->parse(PHP_INT_MAX));
    }

    public function testThatValueUnderMaximumPasses(): void
    {
        $parser = (new AssertInteger())->withMaximum(10);
        self::assertSame(10, $parser->parse(10));
        self::assertSame(1, $parser->parse(1));
        self::assertSame(PHP_INT_MIN, $parser->parse(PHP_INT_MIN));
    }

    public function testDivisibleBy(): void
    {
        self::assertSame(
            10,
            (new AssertInteger())
                ->withDivisibleBy(5)
                ->parse(10)
        );
    }

    public function testDivisibleByDivisorZeroException(): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertInteger())->withDivisibleBy(0);
    }

    public function testDivisibleByDivisorNegativeException(): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertInteger())->withDivisibleBy(-1);
    }

    public function testDivisibleByWrongValueException(): void
    {
        self::expectException(ParsingException::class);
        (new AssertInteger())->withDivisibleBy(10)->parse(9);
    }

}