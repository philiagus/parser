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

use Philiagus\Parser\Parser\AssertSame;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use PHPUnit\Framework\TestCase;

class AssertSameTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertSame()) instanceof Parser);
    }

    /**
     * @return array
     */
    public function notZeroValues(): array
    {
        return [
            '0.0' => [0.0],
            '1' => [1],
            'empty string' => [''],
            'null' => [null],
        ];
    }

    /**
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider notZeroValues
     */
    public function testThatItBlocksNotSameValue($value): void
    {
        self::expectException(ParsingException::class);
        (new AssertSame())->withValue(0)->parse($value);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItAllowsSameValue(): void
    {
        $parser = (new AssertSame())->withValue(0);
        self::assertSame(0, $parser->parse(0));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testExceptionOnMissingConfiguration(): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertSame())->parse(0);
    }

}