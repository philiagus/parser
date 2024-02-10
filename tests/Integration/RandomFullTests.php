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

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Assert\AssertArray;
use Philiagus\Parser\Parser\Assert\AssertFloat;
use Philiagus\Parser\Parser\Assert\AssertInteger;
use Philiagus\Parser\Parser\Assert\AssertNull;
use Philiagus\Parser\Parser\Assert\AssertSame;
use Philiagus\Parser\Parser\Assert\AssertStdClass;
use Philiagus\Parser\Parser\Assert\AssertString;
use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\IgnoreInput;
use Philiagus\Parser\Parser\Logic\Preserve;
use Philiagus\Parser\Parser\Parse\ParseJSONString;
use Philiagus\Parser\Parser\Parse\ParseStdClass;
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
        $parser = ParseJSONString::new()
            ->then(
                Preserve::around(
                    ParseStdClass::new()
                        ->modifyEachPropertyName(IgnoreInput::resultIn('overwritten'))
                        ->modifyEachPropertyValue(IgnoreInput::resultIn('overwritten'))
                        ->modifyPropertyValue('overwritten', AssertSame::value('overwritten')->then(IgnoreInput::resultIn('overwritten again')))
                        ->then(Assign::to($result2))
                        ->then(Append::to($result3))
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
            ->then(Assign::to($preservedValue2));
        try {
            $result = $parser->parse(Subject::default($input, throwOnError: false));
        } catch (ParsingException $e) {
            self::fail(
                $e->getSubject()->getPathAsString(true) . PHP_EOL .
                $e->getSubject()->getPathAsString(false) . PHP_EOL .
                $e->getMessage()
            );
        }

        self::assertSame($preservedValue2, $result->getValue());
        self::assertEquals((object) ['overwritten' => 'overwritten again'], $result2);
        self::assertSame([$result2], $result3);
        self::assertSame($preservedValue1, $preservedValue2);
        self::assertEquals($rawValue, $preservedValue1);
    }
}
