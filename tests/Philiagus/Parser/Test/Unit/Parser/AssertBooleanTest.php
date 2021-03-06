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

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Parser\AssertBoolean;
use Philiagus\Parser\Exception\ParsingException;
use PHPUnit\Framework\TestCase;

class AssertBooleanTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertBoolean()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return (new DataProvider(~DataProvider::TYPE_BOOLEAN))->provide();
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
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return (new DataProvider(DataProvider::TYPE_BOOLEAN))->provide();
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
        (new AssertBoolean())->overwriteTypeExceptionMessage($message)->parse('yes');
    }

    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'hello string string<ASCII>(5)"hello"'
        );
        (new AssertBoolean())
            ->overwriteTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse('hello');
    }

}