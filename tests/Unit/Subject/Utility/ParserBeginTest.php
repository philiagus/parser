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

namespace Philiagus\Parser\Test\Unit\Subject\Utility;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Subject\Utility\ParserBegin;
use Philiagus\Parser\Test\SubjectTestBase;
use Philiagus\Parser\Test\Util;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ParserBegin::class)]
#[CoversClass(Subject::class)]
class ParserBeginTest extends SubjectTestBase
{
    public static function provideConstructorArguments(): array
    {
        $cases = [];
        foreach ((new DataProvider())->provide(false) as $name => $value) {
            foreach (['0 description' => '0', 'no description' => '', 'description' => 'DESCRIPTION'] as $descName => $descValue) {
                foreach (['nothrow' => false, 'throw' => true] as $throwName => $throwValue) {
                    $cases["$throwName $descName $name"] = [$value, $descValue, $throwValue];
                }
            }
        }

        return $cases;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideConstructorArguments')]
    public function testCreation(mixed $value, string $description, bool $throwOnError): void
    {
        $root = Subject::default($value, 'ROOT', $throwOnError);
        $expectedPathPart = $description === '' ? '' : ' ▷' . $description;

        $subject = new ParserBegin($root, $description);
        Util::assertSame($value, $subject->getValue());
        self::assertTrue($subject->isUtility());
        self::assertSame($root, $subject->getSource());
        self::assertSame($description, $subject->getDescription());
        self::assertSame($throwOnError, $subject->throwOnError());
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(true));
        self::assertSame("ROOT", $subject->getPathAsString(false));
        self::assertSame([$root, $subject], $subject->getSubjectChain(true));
        self::assertSame([$root], $subject->getSubjectChain(false));
    }

    protected function createChained(Subject $parent): Subject
    {
        return new ParserBegin($parent, 'description');
    }
}
