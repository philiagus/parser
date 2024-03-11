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
use Philiagus\Parser\Subject\ArrayKey;
use Philiagus\Parser\Test\SubjectTestBase;
use Philiagus\Parser\Test\Util;
use PHPUnit\Framework\Attributes\CoversClass;


#[CoversClass(ArrayKey::class)]
#[CoversClass(Subject::class)]
class ArrayKeyTest extends SubjectTestBase
{
    public static function provideConstructorArguments(): array
    {
        $types = DataProvider::TYPE_INTEGER | DataProvider::TYPE_STRING;

        $cases = [];
        foreach ((new DataProvider($types))->provide(false) as $name => $value) {
            foreach (['nothrow' => false, 'throw' => true] as $throwName => $throwValue) {
                $cases["$throwName $name"] = [$value, $throwValue];
            }
        }

        return $cases;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideConstructorArguments')]
    public function testCreation(mixed $value, bool $throwOnError): void
    {
        $root = Subject::default(null, 'ROOT', $throwOnError);
        $expectedPathPart = " key " . var_export($value, true);

        $subject = new ArrayKey($root, $value);
        Util::assertSame($value, $subject->getValue());
        self::assertFalse($subject->isUtilitySubject());
        self::assertSame($root, $subject->getSourceSubject());
        self::assertSame((string) $value, $subject->getDescription());
        self::assertSame($throwOnError, $subject->throwOnError());
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(true));
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(false));
        self::assertSame([$root, $subject], $subject->getSubjectChain(true));
        self::assertSame([$root, $subject], $subject->getSubjectChain(false));
    }
}
