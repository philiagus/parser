<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Test\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\OneOf;
use PHPUnit\Framework\TestCase;

class OneOfTest extends TestCase
{

    public function testThatItExtendsBaseParser()
    {
        self::assertTrue((new OneOf()) instanceof Parser);
    }

    public function testThatItStopsAtTheFirstMatchingParser()
    {
        self::assertEquals('matched!', (new OneOf())
            ->withOption(
                new class() implements \Philiagus\Parser\Contract\Parser
                {
                    public function parse($value, string $path = '')
                    {
                        throw new ParsingException('Exception', $path);
                    }
                }
            )
            ->withOption(
                new class() implements \Philiagus\Parser\Contract\Parser
                {
                    public function parse($value, string $path = '')
                    {
                        return 'matched!';
                    }
                }
            )
            ->withOption(
                new class() implements \Philiagus\Parser\Contract\Parser
                {
                    public function parse($value, string $path = '')
                    {
                        throw new \LogicException('This code should never be reached');
                    }
                }
            )->parse(null));
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParserConfigurationException
     */
    public function testThatItThrowsExceptionWhenNoOptionsAreDefined()
    {
        (new OneOf())->parse(null);
    }

    /**
     * @expectedException \Philiagus\Parser\Exception\ParsingException
     */
    public function testThatItThrowsAnExceptionWhenNothingMatches()
    {
        (new OneOf())
            ->withOption(
                new class() implements \Philiagus\Parser\Contract\Parser
                {
                    public function parse($value, string $path = '')
                    {
                        throw new ParsingException('Exception', $path);
                    }
                }
            )
            ->parse(null);
    }

}