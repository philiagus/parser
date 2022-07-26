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
use Philiagus\Parser\Parser\Any;
use Philiagus\Parser\Parser\Logic\Chain;
use Philiagus\Parser\Result;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Contract;

/**
 * @covers \Philiagus\Parser\Parser\Logic\Chain
 */
class ChainTest extends ParserTestBase
{
    use ChainableParserTest;

    public function testParsers(): void
    {
        $result1 = new \stdClass();
        $result2 = new \stdClass();
        $result3 = new \stdClass();
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Subject::class,
                        result: fn(Contract\Subject $subject) => new Result($subject, $result1, [])
                    )
                ,
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn() => $result1,
                        Result::class,
                        eligible: fn($_1, $_2, array $successes) => $successes[0],
                        result: fn(Contract\Subject $subject) => new Result($subject, $result2, []),
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn() => $result2,
                        Result::class,
                        eligible: fn($_1, $_2, array $successes) => $successes[0] && $successes[1],
                        result: fn(Contract\Subject $subject) => new Result($subject, $result3, []),
                    ),
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Contract\Subject $subject, Contract\Result $result) use ($result3) {
                    if ($result->getValue() !== $result3) {
                        return ['The result does not match'];
                    }

                    return [];
                }
            );
        $builder->run();
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(static fn($value) => [$value, fn() => Chain::parsers(Any::new()), $value])
            ->provide(false);
    }
}
