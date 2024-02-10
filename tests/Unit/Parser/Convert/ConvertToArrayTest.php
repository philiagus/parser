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

namespace Philiagus\Parser\Test\Unit\Parser\Convert;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\Convert\ConvertToArray;
use Philiagus\Parser\Test\TestBase;

/**
 * @covers \Philiagus\Parser\Parser\Convert\ConvertToArray
 */
class ConvertToArrayTest extends TestBase
{

    public static function provideUsingCast(): array
    {
        return (new DataProvider())
            ->provide();
    }

    public static function provideArrayWithKey(): array
    {
        $cases = [];
        foreach (
            (new DataProvider())
                ->provide(false) as $valueName => $value) {
            foreach ((new DataProvider(DataProvider::TYPE_STRING | DataProvider::TYPE_INTEGER))->provide(false) as $keyName => $key) {
                $cases["$keyName => $valueName"] = [$key, $value];
            }
        }

        return $cases;
    }

    /**
     * @dataProvider provideArrayWithKey
     */
    public function testCreatingArrayWithKey($key, $value)
    {
        self::assertTrue(DataProvider::isSame(
            is_array($value) ? $value : [$key => $value],
            ConvertToArray::creatingArrayWithKey($key)->parse(
                Subject::default($value)
            )->getValue()
        ));
    }

    /**
     * @dataProvider provideUsingCast
     */
    public function testUsingCast($value)
    {
        self::assertTrue(
            DataProvider::isSame(
                (array) $value,
                ConvertToArray::usingCast()
                    ->parse(Subject::default($value))
                    ->getValue()
            )
        );
    }
}