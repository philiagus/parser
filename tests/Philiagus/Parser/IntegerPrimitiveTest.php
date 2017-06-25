<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Philiagus\Test\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\IntegerPrimitive;
use PHPUnit\Framework\TestCase;

class IntegerPrimitiveTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new IntegerPrimitive()) instanceof Parser);
    }

    public function provideInvalidValues()
    {
        return DataProvider::provide(~DataProvider::TYPE_INTEGER);
    }

    /**
     * @param mixed $value
     *
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     * @dataProvider provideInvalidValues
     */
    public function testThatNonIntegersAreBlocked($value)
    {
        (new IntegerPrimitive())->parse($value);
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
    public function testThatIntegersAreAllowed($value)
    {
        $result = (new IntegerPrimitive())->parse($value);
        self::assertSame($result, $value);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParserConfigurationException
     */
    public function testThatMinimumCannotBeGreaterThanMaximum()
    {
        (new IntegerPrimitive())->withMaximum(100)->withMinimum(1000);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParserConfigurationException
     */
    public function testThatMaximumCannotBeLowerThanMinimum()
    {
        (new IntegerPrimitive())->withMinimum(1000)->withMaximum(100);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatValueMustBeGreaterThanMinimum()
    {
        (new IntegerPrimitive())
            ->withMinimum(10)
            ->parse(0);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatValueMustBeLowerThanMaximum()
    {
        (new IntegerPrimitive())
            ->withMaximum(0)
            ->parse(10);
    }

    public function provideOutOfRangeValues()
    {
        return [
            'lower' => [-1],
            'upper' => [11],
        ];
    }

    public function testThatValueOverMinimumPasses()
    {
        $parser = (new IntegerPrimitive())->withMinimum(0);
        self::assertSame(10, $parser->parse(10));
        self::assertSame(0, $parser->parse(0));
        self::assertSame(PHP_INT_MAX, $parser->parse(PHP_INT_MAX));
    }

    public function testThatValueUnderMaximumPasses()
    {
        $parser = (new IntegerPrimitive())->withMaximum(10);
        self::assertSame(10, $parser->parse(10));
        self::assertSame(1, $parser->parse(1));
        self::assertSame(PHP_INT_MIN, $parser->parse(PHP_INT_MIN));
    }

}