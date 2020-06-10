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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Fork;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ForkTest extends TestCase
{
    public function testItExtendsBaseParser(): void
    {
        self::assertTrue(new Fork() instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideAllValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    /**
     * @param $value
     *
     * @dataProvider provideAllValues
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItForksAnyValueToEveryParser($value): void
    {
        $parser = new Fork();
        $path = new Root('root');
        for ($parsers = 0; $parsers < 3; $parsers++) {
            $child = $this->prophesize(ParserContract::class);
            $argument = Argument::that(function ($provided) use ($value) {
                return DataProvider::isSame($value, $provided);
            });
            $child->parse($argument, $path)->shouldBeCalledOnce();
            /** @var ParserContract $childParser */
            $childParser = $child->reveal();
            $parser->addParser($childParser);
        }

        $parser->parse($value, $path);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItStopsAtFirstErrorParser(): void
    {
        $parser = new Fork();

        $child = $this->prophesize(ParserContract::class);
        $child->parse(1, Argument::any())->shouldBeCalledOnce();
        /** @var ParserContract $childParser */
        $childParser = $child->reveal();
        $parser->addParser($childParser);

        $child = $this->prophesize(ParserContract::class);
        $child->parse(1, Argument::any())->shouldBeCalledOnce()->will(
            function ($args) {
                [$value, $path] = $args;
                throw new ParsingException($value, 'error', $path);
            }
        );

        /** @var ParserContract $childParser */
        $childParser = $child->reveal();
        $parser->addParser($childParser);

        $child = $this->prophesize(ParserContract::class);
        $child->parse(1, Argument::any())->shouldBeCalledTimes(0);

        /** @var ParserContract $childParser */
        $childParser = $child->reveal();
        $parser->addParser($childParser);

        $this->expectException(ParsingException::class);
        $parser->parse(1);
    }


}