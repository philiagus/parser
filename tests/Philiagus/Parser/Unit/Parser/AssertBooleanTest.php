<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Parser\AssertBoolean;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertBooleanTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new AssertBoolean()) instanceof Parser);
    }

    public function provideInvalidValues(): array
    {
        return DataProvider::provide(~DataProvider::TYPE_BOOLEAN);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testThatItBlocksNonBooleanValues($value)
    {
        self::expectException(ParsingException::class);
        (new AssertBoolean())->parse($value);
    }

    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_BOOLEAN);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideValidValues
     */
    public function testThatItAllowsBooleanValues($value)
    {
        $result = (new AssertBoolean())->parse($value);
        self::assertSame($value, $result);
    }

}