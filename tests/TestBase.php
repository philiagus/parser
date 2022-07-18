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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class TestBase extends TestCase
{
    use ProphecyTrait;

    public function provideAnything(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->provide();
    }

    public function provideInvalidArrayKeys(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INTEGER & ~DataProvider::TYPE_STRING))
            ->provide();
    }

    protected function prophesizeUncalledParser(): Parser
    {
        $parser = $this->prophesize(Parser::class);
        $parser->parse(Argument::any())->shouldNotBeCalled();
        $parser->parse(Argument::any())->shouldNotBeCalled();

        return $parser->reveal();
    }

    protected function prophesizeParser(
        array $inputResultPairs,
              $expectedPath = null
    ): Parser
    {
        $parser = $this->prophesize(Parser::class);
        foreach ($inputResultPairs as $pair) {
            if (!is_array($pair)) {
                $pair = (array) $pair;
            }
            if (count($pair) === 1) {
                $pair[1] = $pair[0];
            }
            $parser
                ->parse(
                    Argument::that(fn(Subject $subject) => $subject->getValue() === $pair[0])
                )
                ->shouldBeCalled()
                ->will(fn(array $args) => new Result($args[0], $pair[1], []));
        }
        if (empty($inputResultPairs)) {
            $parser->parse(Argument::any())->shouldNotBeCalled();
        }

        return $parser->reveal();
    }

}
