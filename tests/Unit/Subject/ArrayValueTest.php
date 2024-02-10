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

namespace Philiagus\Parser\Test\Unit\Subject;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Test\SubjectTestBase;
use Philiagus\Parser\Test\Util;

/**
 * @covers \Philiagus\Parser\Subject\ArrayValue
 * @covers \Philiagus\Parser\Base\Subject
 */
class ArrayValueTest extends SubjectTestBase
{
    public static function provideConstructorArguments(): array
    {
        $types = DataProvider::TYPE_INTEGER | DataProvider::TYPE_STRING;

        $cases = [];
        foreach ((new DataProvider($types))->provide(false) as $keyName => $keyValue) {
            foreach ((new DataProvider())->provide(false) as $valueName => $valueValue) {
                foreach (['nothrow' => false, 'throw' => true] as $throwName => $throwValue) {
                    $cases["$throwName $keyName $valueName"] = [$keyValue, $valueValue, $throwValue];
                }
            }
        }

        return $cases;
    }

    /**
     * @dataProvider provideConstructorArguments
     */
    public function testCreation(mixed $keyValue, mixed $valueValue, bool $throwOnError): void
    {
        $root = Subject::default(null, 'ROOT', $throwOnError);
        $expectedPathPart = "[$keyValue]";

        $subject = new ArrayValue($root, $keyValue, $valueValue);
        Util::assertSame($valueValue, $subject->getValue());
        self::assertFalse($subject->isUtilitySubject());
        self::assertSame($root, $subject->getSourceSubject());
        self::assertSame((string) $keyValue, $subject->getDescription());
        self::assertSame($throwOnError, $subject->throwOnError());
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(true));
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(false));
        self::assertSame([$root, $subject], $subject->getSubjectChain(true));
        self::assertSame([$root, $subject], $subject->getSubjectChain(false));
    }
}
