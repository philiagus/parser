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
use Philiagus\Parser\Parser\Logic\Preserve;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Utility\Forwarded;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\Mock\ParserMock;
use Philiagus\Parser\Test\ParserTestBase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Preserve::class)]
class PreserveTest extends ParserTestBase
{

    use ChainableParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(static fn($value) => [$value, fn() => Preserve::around(
                (new ParserMock())->acceptAnything()
            ), $value])
            ->provide(false);
    }

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
                        result: fn(Subject $subject) => new Result($subject, !$subject->getValue(), [])
                    )
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Subject $subject, Result $result) {
                    if (!DataProvider::isSame($subject->getValue(), $result->getValue())) {
                        return ['Result value does not match'];
                    }

                    return [];
                }
            );
        $builder->run();
    }
}
