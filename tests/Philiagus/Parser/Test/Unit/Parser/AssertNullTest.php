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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertNull;
use PHPUnit\Framework\TestCase;

class AssertNullTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertNull()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return (new DataProvider(~DataProvider::TYPE_NULL))->provide();
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
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return (new DataProvider(DataProvider::TYPE_NULL))->provide();
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
        (new AssertNull())->overwriteExceptionMessage($msg)->parse(false);
    }

    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'hello string string<ASCII>(5)"hello"'
        );
        (new AssertNull())
            ->overwriteExceptionMessage('{value} {value.type} {value.debug}')
            ->parse('hello');
    }

}