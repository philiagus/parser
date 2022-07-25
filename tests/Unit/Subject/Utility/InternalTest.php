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
use Philiagus\Parser\Subject\Utility\Internal;
use Philiagus\Parser\Test\SubjectTestBase;
use Philiagus\Parser\Test\Util;

/**
 * @covers \Philiagus\Parser\Subject\Utility\Internal
 * @covers \Philiagus\Parser\Base\Subject
 */
class InternalTest extends SubjectTestBase
{
    public function provideConstructorArguments(): array
    {
        $cases = [];
        foreach ((new DataProvider())->provide(false) as $name => $value) {
            foreach (['nothrow' => false, 'throw' => true] as $throwName => $throwValue) {
                $cases["$throwName $name"] = [$value, $throwValue];
            }
        }

        return $cases;
    }

    /**
     * @dataProvider provideConstructorArguments
     */
    public function testCreation(mixed $value, bool $throwOnError): void
    {
        $root = Subject::default(null, 'ROOT', $throwOnError);
        $expectedPathPart = ' DESCRIPTION↩';

        $subject = new Internal($root, 'DESCRIPTION', $value);
        Util::assertSame($value, $subject->getValue());
        self::assertSame('DESCRIPTION', $subject->description);
        self::assertSame($throwOnError, $subject->throwOnError);
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(true));
        self::assertSame("ROOT", $subject->getPathAsString(false));
        self::assertSame([$root, $subject], $subject->getSubjectChain(true));
        self::assertSame([$root], $subject->getSubjectChain(false));
        $builder = $subject->getResultBuilder('builder description');
        Util::assertSame($value, $builder->getValue());
        self::assertSame($builder->getSubject()->description, 'builder description');
    }
}
