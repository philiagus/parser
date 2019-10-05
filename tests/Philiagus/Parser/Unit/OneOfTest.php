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

namespace Philiagus\Test\Parser\Unit;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\MultipleParsingException;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\OneOf;
use Philiagus\Parser\Type\AcceptsMixed;
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
                new class() implements AcceptsMixed
                {
                    public function parse($value, Path $path)
                    {
                        throw new ParsingException('value', 'Exception', $path);
                    }
                }
            )
            ->withOption(
                new class() implements AcceptsMixed
                {
                    public function parse($value, string $path = '')
                    {
                        return 'matched!';
                    }
                }
            )
            ->withOption(
                new class() implements AcceptsMixed
                {
                    public function parse($value, string $path = '')
                    {
                        throw new \LogicException('This code should never be reached');
                    }
                }
            )->parse(null));
    }

    public function testThatItThrowsExceptionWhenNoOptionsAreDefined()
    {
        self::expectException(ParserConfigurationException::class);
        (new OneOf())->parse(null);
    }

    public function testThatItThrowsAnExceptionWhenNothingMatches()
    {
        $exception = new ParsingException('value', 'Exception', '');
        $option = $this->prophesize(AcceptsMixed::class);
        $option->parse(null, '')->willThrow($exception);

        self::expectException(MultipleParsingException::class);
        (new OneOf())
            ->withOption($option->reveal())
            ->parse(null);
    }

}