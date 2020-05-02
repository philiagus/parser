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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\MultipleParsingException;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\OneOf;
use Philiagus\Parser\Path\Root;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class OneOfTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new OneOf()) instanceof Parser);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItStopsAtTheFirstMatchingParser(): void
    {
        self::assertEquals('matched!', (new OneOf())
            ->addSameOption(INF)
            ->addEqualsOption(NAN)
            ->addOption(
                new class() extends Parser
                {
                    public function execute($value, Path $path = null)
                    {
                        throw new ParsingException('value', 'Exception', $path);
                    }
                }
            )
            ->addOption(
                new class() extends Parser
                {
                    public function execute($value, Path $path = null)
                    {
                        return 'matched!';
                    }
                }
            )
            ->addOption(
                new class() extends Parser
                {
                    public function execute($value, Path $path = null)
                    {
                        throw new \LogicException('This code should never be reached');
                    }
                }
            )->parse(null));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItThrowsExceptionWhenNoOptionsAreDefined(): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new OneOf())->parse(null);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItThrowsAnExceptionWhenNothingMatches(): void
    {
        $exception = new ParsingException('value', 'Exception', new Root(''));
        $option = $this->prophesize(Parser::class);
        $option->execute(null, Argument::type(Path::class))->willThrow($exception);
        /** @var Parser $parser */
        $parser = $option->reveal();
        $this->expectException(MultipleParsingException::class);
        (new OneOf())
            ->addOption($parser)
            ->parse(null);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testNonOfExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(MultipleParsingException::class);
        $this->expectExceptionMessage($msg);
        (new OneOf())
            ->addOption(
                new class() extends Parser
                {
                    protected function execute($value, Path $path)
                    {
                        throw new ParsingException($value, 'muh', $path);
                    }
                }
            )
            ->overwriteNonOfExceptionMessage($msg)
            ->parse(null);
    }

    /**
     * @return array
     */
    public function provideEqualsValues(): array
    {
        return [
            ['1', 1],
            [true, 1],
            [false, 0],
            ['1.0', 1.0]
        ];
    }

    /**
     * @param $provided
     * @param $expected
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider  provideEqualsValues
     */
    public function testEqualsOption($provided, $expected): void
    {
        self::assertSame($expected, (new OneOf())->addEqualsOption($provided)->parse($expected));
        self::assertSame($provided, (new OneOf())->addEqualsOption($expected)->parse($provided));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testSameOption(): void
    {
        self::assertSame(1, (new OneOf())->addEqualsOption(1)->parse(1));
    }


    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testEqualsException(): void
    {
        $this->expectException(MultipleParsingException::class);
        (new OneOf())->addEqualsOption(100)->parse(0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testSameException(): void
    {
        $this->expectException(MultipleParsingException::class);
        (new OneOf())->addSameOption(100)->parse(0);
    }

}