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
use Philiagus\Parser\Subject\Root;
use Philiagus\Parser\Test\SubjectTestBase;
use Philiagus\Parser\Test\Util;
use Philiagus\Parser\Util\Debug;

/**
 * @covers \Philiagus\Parser\Subject\Root
 * @covers \Philiagus\Parser\Base\Subject
 */
class RootTest extends SubjectTestBase
{
    public static function provideConstructorArguments(): array
    {
        $cases = [];
        foreach ((new DataProvider())->provide(false) as $name => $value) {
            foreach (['no description' => null, 'description' => 'description'] as $descName => $descValue) {
                foreach (['nothrow' => false, 'throw' => true] as $throwName => $throwValue) {
                    $cases["$descName $throwName $name"] = [$value, $descValue, $throwValue];
                }
            }
        }

        return $cases;
    }

    /**
     * @dataProvider provideConstructorArguments
     */
    public function testCreation(mixed $value, ?string $description, bool $throwOnError): void
    {
        $expectedDescription = $description ?? Debug::getType($value);

        $root = new Root($value, $description, $throwOnError);
        Util::assertSame($value, $root->getValue());
        self::assertFalse($root->isUtilitySubject());
        self::assertSame($expectedDescription, $root->getDescription());
        self::assertSame($throwOnError, $root->throwOnError());
        self::assertSame($expectedDescription, $root->getPathAsString(true));
        self::assertSame($expectedDescription, $root->getPathAsString(false));
        self::assertSame([$root], $root->getSubjectChain(true));
        self::assertSame([$root], $root->getSubjectChain(false));
        $builder = $root->getResultBuilder('builder description');
        Util::assertSame($value, $builder->getValue());
        self::assertSame($builder->getSubject()->getDescription(), 'builder description');


        $root = Subject::default($value, $description, $throwOnError);
        Util::assertSame($value, $root->getValue());
        self::assertFalse($root->isUtilitySubject());
        self::assertSame($expectedDescription, $root->getDescription());
        self::assertSame($throwOnError, $root->throwOnError());
        self::assertSame($expectedDescription, $root->getPathAsString(true));
        self::assertSame($expectedDescription, $root->getPathAsString(false));
        self::assertSame([$root], $root->getSubjectChain(true));
        self::assertSame([$root], $root->getSubjectChain(false));
        $builder = $root->getResultBuilder('builder description');
        Util::assertSame($value, $builder->getValue());
        self::assertSame($builder->getSubject()->getDescription(), 'builder description');
    }
}
