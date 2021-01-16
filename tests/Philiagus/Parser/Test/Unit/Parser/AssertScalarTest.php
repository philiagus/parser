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
use Philiagus\Parser\Parser\AssertScalar;
use PHPUnit\Framework\TestCase;

class AssertScalarTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertScalar()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return (new DataProvider(~DataProvider::TYPE_SCALAR))->provide();
    }

    /**
     * @param mixed $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideInvalidValues
     */
    public function testThatItBlocksNonScalarValues($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertScalar())->parse($value);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return (new DataProvider(DataProvider::TYPE_SCALAR))->provide();
    }

    /**
     * @param mixed $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideValidValues
     */
    public function testThatItAllowsScalarValues($value): void
    {
        self::assertTrue(DataProvider::isSame($value, (new AssertScalar())->parse($value)));
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
        (new AssertScalar())->overwriteExceptionMessage($msg)->parse(new \stdClass());
    }

    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'Array array array(0)'
        );
        (new AssertScalar())
            ->overwriteExceptionMessage('{value} {value.type} {value.debug}')
            ->parse([]);
    }

}