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
use Philiagus\Parser\Subject\PropertyValue;
use Philiagus\Parser\Test\SubjectTestBase;
use Philiagus\Parser\Test\Util;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PropertyValue::class)]
#[CoversClass(Subject::class)]
class PropertyValueTest extends SubjectTestBase
{
    public static function provideConstructorArguments(): \Generator
    {
        foreach ((new DataProvider())->provide(false) as $valueName => $valueValue) {
            foreach (['nothrow' => false, 'throw' => true] as $throwName => $throwValue) {
                yield "$throwName $valueName" => ['property name', $valueValue, $throwValue];
            }
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideConstructorArguments')]
    public function testCreation(mixed $keyValue, mixed $valueValue, bool $throwOnError): void
    {
        $root = Subject::default(null, 'ROOT', $throwOnError);
        $expectedPathPart = is_string($keyValue) && preg_match('/\W/', $keyValue)
            ? "[$keyValue]"
            : ".$keyValue";

        $subject = new PropertyValue($root, $keyValue, $valueValue);
        Util::assertSame($valueValue, $subject->getValue());
        self::assertFalse($subject->isUtility());
        self::assertSame($root, $subject->getSource());
        self::assertSame((string)$keyValue, $subject->getDescription());
        self::assertSame($throwOnError, $subject->throwOnError());
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(true));
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(false));
        self::assertSame([$root, $subject], $subject->getSubjectChain(true));
        self::assertSame([$root, $subject], $subject->getSubjectChain(false));
    }

    protected function createChained(Subject $parent): Subject
    {
        return new PropertyValue($parent, 'prop name', 'prop value');
    }
}
