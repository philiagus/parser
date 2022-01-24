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

namespace Philiagus\Parser\Test\Unit\Parser\Logic;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Parser\Logic\Fork;
use Philiagus\Parser\Test\ChainableParserTest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ForkTest extends TestCase
{
    use ChainableParserTest;

    public function provideAnyValue(): array
    {
        return (new DataProvider())->provide();
    }

    /**
     * @param $value
     *
     * @return void
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @throws \Philiagus\Parser\Exception\ParsingException
     * @dataProvider provideAnyValue
     */
    public function testFull($value): void
    {
        $parser1 = $this->prophesize(Parser::class);
        $parser1
            ->parse(
                Argument::that(
                    fn($arg) => DataProvider::isSame($arg, $value)
                ),
                Argument::any()
            )
            ->shouldBeCalled()
            ->willReturn(1);
        $parser1 = $parser1->reveal();

        $parser2 = $this->prophesize(Parser::class);
        $parser2
            ->parse(
                Argument::that(
                    fn($arg) => DataProvider::isSame($arg, $value)
                ),
                Argument::any()
            )
            ->shouldBeCalled()
            ->willReturn(2);
        $parser2 = $parser2->reveal();

        $parser3 = $this->prophesize(Parser::class);
        $parser3
            ->parse(
                Argument::that(
                    fn($arg) => DataProvider::isSame($arg, $value)
                ),
                Argument::any()
            )
            ->shouldBeCalled()
            ->willReturn(3);
        $parser3 = $parser3->reveal();

        self::assertTrue(
            DataProvider::isSame(
                $value,
                Fork::to($parser1, $parser2)
                    ->addParser($parser3)
                    ->parse($value)
            )
        );
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        $parser = Fork::to();
        return (new DataProvider())
            ->map(fn($value) => [$value, $parser, $value])
            ->provide(false);
    }
}