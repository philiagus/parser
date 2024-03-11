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
use Philiagus\Parser\Parser\Assert\AssertArray;
use Philiagus\Parser\Parser\Extract\Append;
use Philiagus\Parser\Parser\Logic\Unique;
use Philiagus\Parser\Test\TestBase;
use PHPUnit\Framework\Attributes\CoversClass;

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
        yield 'non strict' => [
            [1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            false,
            [1, 2, 3, 4, 5]
        ];
        yield 'strict with mixed types' => [
            [1, '2', 3, '4', 5, '1', 2, '3', 4, '5'],
            true,
            [1, '2', 3, '4', 5, '1', 2, '3', 4, '5'],
        ];
        yield 'non with mixed types' => [
            [1, '2', 3, '4', 5, '1', 2, '3', 4, '5'],
            false,
            [1, '2', 3, '4', 5],
        ];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('provideCases')]
    public function testFull(
        array $input,
        bool  $strict,
        array $expected
    ): void
    {
        $appender = Append::to($result);
        AssertArray::new()
            ->giveEachValue(
                $strict ?
                    Unique::comparingSame($appender) :
                    Unique::comparingEquals($appender)
            )
            ->parse(Subject::default($input));

        self::assertSame($expected, $result);
    }

}
