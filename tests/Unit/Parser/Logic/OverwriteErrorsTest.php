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
use Philiagus\Parser\Parser\Logic\OverwriteErrors;
use Philiagus\Parser\Result;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\Mock\ParserMock;
use Philiagus\Parser\Test\ParserTestBase;

/**
 * @covers \Philiagus\Parser\Parser\Logic\OverwriteErrors
 */
class OverwriteErrorsTest extends ParserTestBase
{

    use ChainableParserTest;

    public function testWithMessage(): void
    {
        $alteredResult = new \stdClass();
        $builder = $this->builder();

        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->messageArgument()
                    ->expectedWhen(
                        fn($_1, $_2, array $successes) => !$successes[1]
                    ),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn() => fn() => true,
                        fn() => fn() => true,
                        result: fn(Subject $subject) => new Result($subject, $alteredResult, [])
                    )
                    ->errorWillBeHidden()
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) use ($alteredResult) {
                    if ($result->getValue() !== $alteredResult) {
                        return ['Result does not match expected format'];
                    }

                    return [];
                }
            );

        $builder->run();
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(static fn($value) => [
                $value,
                fn() => OverwriteErrors::withMessage(
                    'message',
                    (new ParserMock())
                        ->expect(
                            static fn() => true,
                            static fn() => true,
                            fn(Subject $subject) => new Result($subject, $subject->getValue(), [])
                        )
                ),
                $value,
                false,
            ])
            ->provide(false);
    }
}
