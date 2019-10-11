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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertInfinite;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertInfiniteTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertInfinite()) instanceof Parser);
    }

    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int)~DataProvider::TYPE_INFINITE);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testThatItBlocksNonInfiniteValues($value): void
    {
        self::expectException(ParsingException::class);
        (new AssertInfinite())->parse($value);
    }

    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_INFINITE);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideValidValues
     */
    public function testThatItAllowsInfiniteValues($value): void
    {
        $result = (new AssertInfinite())->parse($value);
        self::assertSame($value, $result);
    }

}