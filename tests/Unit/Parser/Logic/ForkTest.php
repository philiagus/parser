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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Logic\Fork;
use Philiagus\Parser\Result;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\TestBase;
use Prophecy\Argument;

class ForkTest extends TestBase
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
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideAnyValue
     */
    public function testFull($value): void
    {
        $parser = function ($result) use ($value): Parser {
            $parser = $this->prophesize(Parser::class);
            $parser
                ->parse(
                    Argument::that(
                        fn(Subject $subject) => DataProvider::isSame($subject->getValue(), $value)
                    )
                )
                ->shouldBeCalled()
                ->will(
                    function (array $args) use ($result) {
                        return new Result($args[0], $result, []);
                    }
                );

            return $parser->reveal();
        };

        self::assertTrue(
            DataProvider::isSame(
                $value,
                Fork::to($parser(1), $parser(2))
                    ->addParser($parser(3))
                    ->parse(Subject::default($value))
                    ->getValue()
            )
        );
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(fn($value) => [$value, fn() => Fork::to(), $value])
            ->provide(false);
    }
}
