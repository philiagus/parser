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

use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertArray;
use Philiagus\Parser\Parser\AssertFloat;
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Parser\Parser\AssertNull;
use Philiagus\Parser\Parser\AssertSame;
use Philiagus\Parser\Parser\AssertStdClass;
use Philiagus\Parser\Parser\AssertString;
use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\Fixed;
use Philiagus\Parser\Parser\Logic\Fail;
use Philiagus\Parser\Parser\Logic\Preserve;
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
        try {
            $result = ParseJSONString::new()
                ->then(
                    Preserve::around(
                        ParseStdClass::new()
                            ->modifyEachPropertyName(Fixed::value('overwritten'))
                            ->modifyEachPropertyValue(Fixed::value('overwritten'))
                            ->modifyPropertyValue('overwritten', AssertSame::value('overwritten')->then(Fixed::value('overwritten again')))
                            ->then(Assign::to($result2))
                            ->then(Append::to($result3))
                            ->then(Fail::message('boom'))
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
                        ->then(Assign::to($preservedValue1))
                )
                ->then(Assign::to($preservedValue2))
                ->parse($input);
        } catch (ParsingException $e) {
            echo $e->getPath()->toString(true), PHP_EOL,
            $e->getPath()->toString(false), PHP_EOL,
            $e->getMessage();
            self::fail();
        }

        self::assertSame($preservedValue2, $result);
        self::assertEquals((object) ['overwritten' => 'overwritten again'], $result2);
        self::assertSame([$result2], $result3);
        self::assertSame($preservedValue1, $preservedValue2);
        self::assertEquals($rawValue, $preservedValue1);
    }
}
