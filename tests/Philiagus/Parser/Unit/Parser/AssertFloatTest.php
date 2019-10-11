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

use Exception;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertFloat;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertFloatTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertFloat()) instanceof Parser);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int)~DataProvider::TYPE_FLOAT);
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideInvalidValues
     */
    public function testThatNonFloatsAreBlocked($value): void
    {
        self::expectException(ParsingException::class);
        (new AssertFloat())->parse($value);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_FLOAT);
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidValues
     */
    public function testThatFloatsAreAllowed($value): void
    {
        $result = (new AssertFloat())->parse($value);
        self::assertSame($result, $value);
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatMinimumCannotBeGreaterThanMaximum(): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertFloat())->withMaximum(100)->withMinimum(1000);
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatMaximumCannotBeLowerThanMinimum(): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertFloat())->withMinimum(1000.0)->withMaximum(100.0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueMustBeGreaterThanMinimum(): void
    {
        self::expectException(ParsingException::class);
        (new AssertFloat())
            ->withMinimum(10.0)
            ->parse(0.0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueMustBeLowerThanMaximum(): void
    {
        self::expectException(ParsingException::class);
        (new AssertFloat())
            ->withMaximum(0.0)
            ->parse(10.0);
    }

    /**
     * @return array
     */
    public function provideOutOfRangeValues(): array
    {
        return [
            'lower' => [-1.0],
            'upper' => [11.0],
        ];
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueOverMinimumPasses(): void
    {
        $parser = (new AssertFloat())->withMinimum(0.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(0.0, $parser->parse(0.0));
        self::assertSame((float) PHP_INT_MAX, $parser->parse((float) PHP_INT_MAX));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueUnderMaximumPasses(): void
    {
        $parser = (new AssertFloat())->withMaximum(10.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(1.0, $parser->parse(1.0));
        self::assertSame((float) PHP_INT_MIN, $parser->parse((float) PHP_INT_MIN));
    }

}