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
use Philiagus\Parser\Parser\AssertBoolean;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertBooleanTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertBoolean()) instanceof Parser);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int)~DataProvider::TYPE_BOOLEAN);
    }

    /**
     * @param mixed $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideInvalidValues
     */
    public function testThatItBlocksNonBooleanValues($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertBoolean())->parse($value);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_BOOLEAN);
    }

    /**
     * @param mixed $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideValidValues
     */
    public function testThatItAllowsBooleanValues($value): void
    {
        $result = (new AssertBoolean())->parse($value);
        self::assertSame($value, $result);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItUsesTheSpecifiedExceptionMessage(): void
    {
        $message = 'This is an error msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($message);
        (new AssertBoolean())->withTypeExceptionMessage($message)->parse('yes');
    }

}