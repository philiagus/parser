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
use Philiagus\Parser\Subject\PropertyName;
use Philiagus\Parser\Test\SubjectTestBase;
use Philiagus\Parser\Test\Util;

/**
 * @covers \Philiagus\Parser\Subject\PropertyName
 * @covers \Philiagus\Parser\Base\Subject
 */
class PropertyNameTest extends SubjectTestBase
{
    public static function provideConstructorArguments(): array
    {
        $types = DataProvider::TYPE_STRING;

        $cases = [];
        foreach ((new DataProvider($types))->provide(false) as $name => $value) {
            foreach (['nothrow' => false, 'throw' => true] as $throwName => $throwValue) {
                $cases["$throwName $name"] = [$value, $throwValue];
            }
        }

        return $cases;
    }

    /**
     * @dataProvider provideConstructorArguments
     */
    public function testCreation(string $propertyName, bool $throwOnError): void
    {
        $root = Subject::default(null, 'ROOT', $throwOnError);
        $expectedPathPart = preg_match('/\s/', $propertyName)
            ? " property name " . var_export($propertyName, true)
            : " property name {$propertyName}";

        $subject = new PropertyName($root, $propertyName);
        Util::assertSame($propertyName, $subject->getValue());
        self::assertFalse($subject->isUtilitySubject());
        self::assertSame($root, $subject->getSourceSubject());
        self::assertSame((string) $propertyName, $subject->getDescription());
        self::assertSame($throwOnError, $subject->throwOnError());
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(true));
        self::assertSame("ROOT$expectedPathPart", $subject->getPathAsString(false));
        self::assertSame([$root, $subject], $subject->getSubjectChain(true));
        self::assertSame([$root, $subject], $subject->getSubjectChain(false));
        $builder = $subject->getResultBuilder('builder description');
        Util::assertSame($propertyName, $builder->getValue());
        self::assertSame($builder->getSubject()->getDescription(), 'builder description');
    }
}
