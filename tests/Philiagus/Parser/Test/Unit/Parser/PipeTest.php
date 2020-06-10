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
use Philiagus\Parser\Parser\Pipe;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class PipeTest extends TestCase
{
    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new Pipe()) instanceof Parser);
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
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideAllValues
     */
    public function testThatEmptyPipeReturnsWithoutAlteration($value): void
    {
        $result = (new Pipe())->parse($value);
        DataProvider::assertSame($value, $result);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItPerformTheEntirePipe(): void
    {
        $path = new Root('root');
        $pipeParser = $this->prophesize(ParserContract::class);
        $pipeParser->parse(1, $path)->shouldBeCalledOnce()->willReturn(2);
        $pipeParser->parse(2, $path)->shouldBeCalledOnce()->willReturn(3);
        $pipeParser->parse(3, $path)->shouldBeCalledOnce()->willReturn('last');
        $pipeParser->parse('last', $path)->shouldBeCalledOnce()->willReturn('end value');
        $pipeParser = $pipeParser->reveal();
        /** @var ParserContract $pipeParser */
        $result = (new Pipe())
            ->add($pipeParser)
            ->add($pipeParser)
            ->add($pipeParser)
            ->add($pipeParser)
            ->parse(1, $path);
        self::assertSame('end value', $result);
    }


}