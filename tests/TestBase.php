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
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class TestBase extends TestCase
{
    use ProphecyTrait;

    public static function provideAnything(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->provide();
    }

    public static function provideInvalidArrayKeys(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INTEGER & ~DataProvider::TYPE_STRING))
            ->provide();
    }

    public static function getCoveredClass(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $covers = $reflection->getAttributes(CoversClass::class);
        if (empty($covers)) {
            self::fail('Covered class of ' . static::class . ' cannot be determined');
        }

        return $covers[0]->newInstance()->className();
    }

    protected static function prophesizeParserStatic(array $inputResultParis): Parser
    {
        return new class($inputResultParis) implements Parser {

            public function __construct(
                private readonly array $pairs
            )
            {
            }

            #[\Override] public function parse(Contract\Subject $subject): Contract\Result
            {
                foreach ($this->pairs as [$from, $to]) {
                    if ($subject->getValue() === $from) {
                        return new Result($subject, $to, []);
                    }
                }

                Assert::fail("Unmatched value from prophesizeParserStatic");
            }
        };
    }

    protected function prophesizeUncalledParser(): Parser
    {
        $parser = $this->prophesize(Parser::class);
        $parser->parse(Argument::any())->shouldNotBeCalled();
        $parser->parse(Argument::any())->shouldNotBeCalled();

        return $parser->reveal();
    }

    protected function prophesizeParser(array $inputResultPairs): Parser
    {
        $parser = $this->prophesize(Parser::class);
        foreach ($inputResultPairs as $pair) {
            if (!is_array($pair)) {
                $pair = (array)$pair;
            }
            if (count($pair) === 1) {
                $pair[1] = $pair[0];
            }
            /** @noinspection PhpParamsInspection */
            $parser
                ->parse(
                    Argument::that(fn(Contract\Subject $subject) => $subject->getValue() === $pair[0])
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
