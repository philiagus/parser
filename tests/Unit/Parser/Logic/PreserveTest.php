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
use Philiagus\Parser\Contract;
use Philiagus\Parser\Parser\Logic\Preserve;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Utility\Forwarded;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\Mock\ParserMock;
use Philiagus\Parser\Test\ParserTestBase;

/**
 * @covers \Philiagus\Parser\Parser\Logic\Preserve
 */
class PreserveTest extends ParserTestBase
{

    use ChainableParserTest;

    public function testAround(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => $value,
                        Forwarded::class,
                        result: fn(Contract\Subject $subject) => new Result($subject, !$subject->getValue(), [])
                    )
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Contract\Subject $subject, Contract\Result $result) {
                    if (!DataProvider::isSame($subject->getValue(), $result->getValue())) {
                        return ['Result value does not match'];
                    }

                    return [];
                }
            );
        $builder->run();
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(static fn($value) => [$value, fn() => Preserve::around(
                (new ParserMock())->acceptAnything()
            ), $value])
            ->provide(false);
    }
}
