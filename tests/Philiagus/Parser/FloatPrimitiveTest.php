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
use Philiagus\Parser\FloatPrimitive;
use PHPUnit\Framework\TestCase;

class FloatPrimitiveTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new FloatPrimitive()) instanceof Parser);
    }

    public function provideInvalidValues()
    {
        return DataProvider::provide(~DataProvider::TYPE_FLOAT);
    }

    /**
     * @param mixed $value
     *
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     * @dataProvider provideInvalidValues
     */
    public function testThatNonFloatsAreBlocked($value)
    {
        (new FloatPrimitive())->parse($value);
    }

    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_FLOAT);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideValidValues
     */
    public function testThatFloatsAreAllowed($value)
    {
        $result = (new FloatPrimitive())->parse($value);
        self::assertSame($result, $value);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParserConfigurationException
     */
    public function testThatMinimumCannotBeGreaterThanMaximum()
    {
        (new FloatPrimitive())->withMaximum(100)->withMinimum(1000);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParserConfigurationException
     */
    public function testThatMaximumCannotBeLowerThanMinimum()
    {
        (new FloatPrimitive())->withMinimum(1000.0)->withMaximum(100.0);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatValueMustBeGreaterThanMinimum()
    {
        (new FloatPrimitive())
            ->withMinimum(10.0)
            ->parse(0.0);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatValueMustBeLowerThanMaximum()
    {
        (new FloatPrimitive())
            ->withMaximum(0.0)
            ->parse(10.0);
    }

    public function provideOutOfRangeValues()
    {
        return [
            'lower' => [-1.0],
            'upper' => [11.0],
        ];
    }

    public function testThatValueOverMinimumPasses()
    {
        $parser = (new FloatPrimitive())->withMinimum(0.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(0.0, $parser->parse(0.0));
        self::assertSame((float) PHP_INT_MAX, $parser->parse((float) PHP_INT_MAX));
    }

    public function testThatValueUnderMaximumPasses()
    {
        $parser = (new FloatPrimitive())->withMaximum(10.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(1.0, $parser->parse(1.0));
        self::assertSame((float) PHP_INT_MIN, $parser->parse((float) PHP_INT_MIN));
    }

}