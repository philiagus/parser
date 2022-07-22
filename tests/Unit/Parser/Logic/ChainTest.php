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
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Chain;
use Philiagus\Parser\Test\ParserTestBase;

/**
 * @covers \Philiagus\Parser\Parser\Logic\Chain
 */
class ChainTest extends ParserTestBase
{
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
                        result: fn(Subject $subject) => new Result($subject, $result1, [])
                    )
                ,
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn() => $result1,
                        Chain::class,
                        eligible: fn($_1, $_2, array $successes) => $successes[0],
                        result: fn(Subject $subject) => new Result($subject, $result2, []),
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn() => $result2,
                        Chain::class,
                        eligible: fn($_1, $_2, array $successes) => $successes[0] && $successes[1],
                        result: fn(Subject $subject) => new Result($subject, $result3, []),
                    ),
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) use ($result3) {
                    if ($result->getValue() !== $result3) {
                        return ['The result does not match'];
                    }

                    return [];
                }
            );
        $builder->run();
    }
}
