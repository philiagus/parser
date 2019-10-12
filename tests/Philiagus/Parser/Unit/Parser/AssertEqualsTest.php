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

use Philiagus\Parser\Parser\AssertEquals;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use PHPUnit\Framework\TestCase;

class AssertEqualsTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertEquals()) instanceof Parser);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItBlocksNotEqualValues(): void
    {
        $this->expectException(ParsingException::class);
        (new AssertEquals())->withValue(1)->parse(2);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItAllowsEqualValues(): void
    {
        $parser = (new AssertEquals())->withValue(0);
        self::assertSame(0, $parser->parse(0));
        self::assertSame(null, $parser->parse(null));
        self::assertSame('', $parser->parse(''));
        self::assertSame(0.0, $parser->parse(0.0));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testExceptionOnMissingConfiguration(): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertEquals())->parse(0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new AssertEquals())->withValue(false, $msg)->parse(true);
    }

}