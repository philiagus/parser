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

namespace Philiagus\Parser\Test\Integration;

use Philiagus\Parser\Parser\AssertArray;
use Philiagus\Parser\Parser\AssertFloat;
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Parser\Parser\AssertNull;
use Philiagus\Parser\Parser\AssertSame;
use Philiagus\Parser\Parser\AssertStdClass;
use Philiagus\Parser\Parser\AssertString;
use Philiagus\Parser\Parser\Fixed;
use Philiagus\Parser\Parser\Logic\Preserve;
use Philiagus\Parser\Parser\ParseArray;
use Philiagus\Parser\Parser\ParseJSONString;
use Philiagus\Parser\Parser\ParseStdClass;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class RandomFullTests extends TestCase
{
    public function test()
    {
        $rawValue = (object) [
            'string' => 'string',
            'integer' => 1,
            'float' => 1.0,
            'null' => null,
            'stdClass' => new \stdClass(),
            'array' => [],
        ];
        $input = json_encode($rawValue, JSON_PRESERVE_ZERO_FRACTION);

        $result = ParseJSONString::new()
            ->then(
                Preserve::around(
                    ParseStdClass::new()
                        ->modifyEachPropertyName(Fixed::value('overwritten'))
                        ->modifyEachPropertyValue(Fixed::value('overwritten'))
                        ->modifyPropertyValue('overwritten', AssertSame::value('overwritten')->then(Fixed::value('overwritten again')))
                        ->thenAssignTo($result2)
                        ->thenAppendTo($result3)
                )
                    ->then(
                        ParseStdClass::new()
                            ->givePropertyValue('string', AssertString::new())
                            ->givePropertyValue('integer', AssertInteger::new())
                            ->givePropertyValue('float', AssertFloat::new())
                            ->givePropertyValue('stdClass', AssertStdClass::new())
                            ->givePropertyValue('array', AssertArray::new())
                            ->givePropertyValue('null', AssertNull::new())
                    )
                    ->thenAssignTo($preservedValue1)
            )
            ->thenAssignTo($preservedValue2)
            ->parse($input);

        self::assertSame($preservedValue2, $result);
        self::assertEquals((object) ['overwritten' => 'overwritten again'], $result2);
        self::assertSame([$result2], $result3);
        self::assertSame($preservedValue1, $preservedValue2);
        self::assertEquals($rawValue, $preservedValue1);
    }
}
