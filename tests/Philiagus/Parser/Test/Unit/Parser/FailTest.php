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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Fail;
use PHPUnit\Framework\TestCase;

class FailTest extends TestCase
{
    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new Fail()) instanceof Parser);
    }

    public function provideAnyValue(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))->provide();
    }

    /**
     * @param $value
     *
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @throws \Philiagus\Parser\Exception\ParsingException
     * @dataProvider provideAnyValue
     */
    public function testItFailsOnAnyValue($value): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('This value can never match');
        Fail::new()->parse($value);
    }

    public function testMessageOverwriteAndReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('1 integer integer 1');
        Fail::message('{value} {value.type} {value.debug}')->parse(1);
    }

    public function testStaticMessageconstructor(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('1 integer integer 1');
        (new Fail())
            ->overwriteExceptionMessage('{value} {value.type} {value.debug}')
            ->parse(1);
    }

}
