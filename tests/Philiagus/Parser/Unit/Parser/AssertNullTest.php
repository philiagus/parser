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

use Exception;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertNull;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertNullTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertNull()) instanceof Parser);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int)~DataProvider::TYPE_NULL);
    }

    /**
     * @param mixed $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideInvalidValues
     */
    public function testThatItBlocksNonNullValues($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertNull())->parse($value);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_NULL);
    }

    /**
     * @param mixed $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideValidValues
     */
    public function testThatItAllowsNullValues($value): void
    {
        $result = (new AssertNull())->parse($value);
        self::assertSame($value, $result);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new AssertNull())->withExceptionMessage($msg)->parse(false);
    }

}