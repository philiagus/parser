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

namespace Philiagus\Parser\Test;

use DateTimeInterface;
use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

trait ValidValueParserTest
{

    abstract public function provideValidValuesAndParsersAndResults(): array;

    /**
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThatItAcceptsValidValuesThrowing($value, \Closure $parser, $expected, bool $resultWillBeWrapped = true): void
    {
        $subject = Subject::default($value);
        /** @var Result $result */
        $result = $parser($value)->parse($subject);
        self::assertTrue($result->isSuccess());
        self::assertSame($subject, $resultWillBeWrapped ? $result->getSubject()->getParent() : $result->getSubject());
        self::assertSame([], $result->getErrors());
        $resultValue = $result->getValue();
        self::assertTrue(
            $resultValue instanceof DateTimeInterface && $expected instanceof DateTimeInterface ?
                $resultValue::class === $expected::class && $resultValue->format('Y-m-d H:i:s.u') == $expected->format('Y-m-d H:i:s.u') :
                DataProvider::isSame($expected, $resultValue),
            Debug::stringify($expected) . ' is not equal to ' . Debug::stringify($resultValue)
        );
    }

    abstract public static function assertTrue($condition, string $message = ''): void;

    /**
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThatItAcceptsValidValuesNotThrowing($value, \Closure $parser, $expected, bool $resultWillBeWrapped = true): void
    {
        $subject = Subject::default($value, false);
        /** @var Result $result */
        $result = $parser($value)->parse($subject);
        self::assertTrue($result->isSuccess());
        self::assertSame($subject, $resultWillBeWrapped ? $result->getSubject()->getParent() : $result->getSubject());
        self::assertSame([], $result->getErrors());
        $resultValue = $result->getValue();
        self::assertTrue(
            $resultValue instanceof DateTimeInterface && $expected instanceof DateTimeInterface ?
                $resultValue::class === $expected::class && $resultValue->format('Y-m-d H:i:s.u') == $expected->format('Y-m-d H:i:s.u') :
                DataProvider::isSame($expected, $resultValue),
            Debug::stringify($expected) . ' is not equal to ' . Debug::stringify($resultValue)
        );
    }

}
