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

namespace Philiagus\Test\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\NanPrimitive;
use PHPUnit\Framework\TestCase;

class NanPrimitiveTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new NanPrimitive()) instanceof Parser);
    }

    public function provideInvalidValues(): array
    {
        return DataProvider::provide(~DataProvider::TYPE_NAN);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatItBlocksNonNanValues($value)
    {
        (new NanPrimitive())->parse($value);
    }

    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_NAN);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideValidValues
     */
    public function testThatItAllowsNanValues($value)
    {
        $result = (new NanPrimitive())->parse($value);
        self::assertSame($value, $result);
    }

}