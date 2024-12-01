<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Parser\Logic;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Parser\Assert\AssertArray;
use Philiagus\Parser\Parser\Extract\Append;
use Philiagus\Parser\Parser\Logic\Unique;
use Philiagus\Parser\Test\TestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Unique::class)]
class UniqueTest extends TestBase
{

    public static function provideCases(): \Generator
    {
        yield 'strict' => [
            [1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            true,
            [1, 2, 3, 4, 5]
        ];
        yield 'non-strict' => [
            [1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            false,
            [1, 2, 3, 4, 5]
        ];
        yield 'strict with mixed types' => [
            [1, '2', 3, '4', 5, '1', 2, '3', 4, '5'],
            true,
            [1, '2', 3, '4', 5, '1', 2, '3', 4, '5'],
        ];
        yield 'non-strict with mixed types' => [
            [1, '2', 3, '4', 5, '1', 2, '3', 4, '5'],
            false,
            [1, '2', 3, '4', 5],
        ];
        yield 'compare with closure' => [
            [1, 0, '2', 3, 5, '4', 5, '1', 2, '3', 4, '5'],
            static fn(mixed $existing, mixed $candidate) => abs($existing - $candidate) < 2,
            [1, 3, 5],
        ];
    }


    #[DataProvider('provideCases')]
    public function testFull(
        array         $input,
        \Closure|bool $comparison,
        array         $expected
    ): void
    {
        $appender = Append::to($result);
        AssertArray::new()
            ->giveEachValue(
                is_bool($comparison) ?
                    (
                    $comparison ?
                        Unique::comparingSame($appender) :
                        Unique::comparingEquals($appender)
                    ) :
                    Unique::comparingBy($comparison, $appender)
            )
            ->parse(Subject::default($input));

        self::assertSame($expected, $result);
    }

    public function testClosureException()
    {
        $appender = Append::to($result);
        $parser = AssertArray::new()
            ->giveEachValue(
                Unique::comparingBy(
                    fn() => throw new \Error('Type error?'),
                    $appender
                )
            );

        $this->expectException(ParserConfigurationException::class);
        $parser->parse(Subject::default(['nothing happens', 'boom!!!']));
    }

}
