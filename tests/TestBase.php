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

namespace Philiagus\Parser\Test;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class TestBase extends TestCase
{
    public function provideAnything(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->provide();
    }

    protected function prophesizeUncalledParser(): Parser
    {
        $parser = $this->prophesize(Parser::class);
        $parser->parse(Argument::any(), Argument::any())->shouldNotBeCalled();
        $parser->parse(Argument::any())->shouldNotBeCalled();
        return $parser->reveal();
    }

    public function provideInvalidArrayKeys(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INTEGER & ~DataProvider::TYPE_STRING))
            ->provide();
    }

    protected function prophesizeParser(
        array $inputResultPairs,
              $expectedPath = null
    ): Parser
    {
        $parser = $this->prophesize(Parser::class);
        foreach ($inputResultPairs as $pair) {
            if(!is_array($pair)) {
                $pair = (array)$pair;
            }
            if (count($pair) === 1) {
                $pair[1] = $pair[0];
            }
            $parser
                ->parse(
                    $pair[0],
                    $expectedPath ?? Argument::that(fn($arg) => $arg === null || $arg instanceof Path)
                )
                ->shouldBeCalled()
                ->willReturn($pair[1]);
        }
        if(empty($inputResultPairs)) {
            $parser->parse(Argument::any(), Argument::any())->shouldNotBeCalled();
        }

        return $parser->reveal();
    }

}
