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

namespace Philiagus\Parser\Test\Unit;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base;
use Philiagus\Parser\Error;
use Philiagus\Parser\Result;
use Philiagus\Parser\Test\Mock\SubjectMock;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\Util;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Result::class)]
class ResultTest extends TestBase
{
    public static function provideConstructorArguments(): array
    {
        $cases = [];
        foreach ((new DataProvider())->provide(false) as $name => $value) {
            foreach (['nothrow' => false, 'throw' => true] as $throwName => $throwValue) {
                $cases["$throwName $name"] = [$value, $throwValue];
            }
        }

        return $cases;
    }

    public function testSuccess(): void
    {
        $subject = new SubjectMock();
        $memory = $subject->getFullMemory();
        $value = new \stdClass();
        $result = new Result(
            $subject,
            $value,
            []
        );
        self::assertTrue($result->isSuccess());
        self::assertFalse($result->hasErrors());
        self::assertEmpty($result->getErrors());
        self::assertSame($memory, $result->getFullMemory());
        self::assertSame($value, $result->getValue());
    }

    public function testError(): void
    {
        $subject = new SubjectMock();
        $memory = $subject->getFullMemory();
        $result = new Result(
            $subject,
            null,
            $errors = [new Error($subject, 'error')]
        );

        self::assertFalse($result->isSuccess());
        self::assertTrue($result->hasErrors());
        self::assertSame($errors, $result->getErrors());
        self::assertSame($memory, $result->getFullMemory());
        self::expectException(\LogicException::class);
        $result->getValue();
    }

    public function testExceptionOnNonError(): void
    {
        $subject = new SubjectMock();
        self::expectException(\LogicException::class);
        /** @noinspection PhpParamsInspection */
        new Result($subject, null, ['invalid']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideConstructorArguments')]
    public function testCreation(mixed $value, bool $throwOnError): void
    {
        $root = Base\Subject::default(null, 'ROOT', $throwOnError);

        $subject = new Result($root, $value, []);
        Util::assertSame($value, $subject->getValue());
        self::assertSame('', $subject->getDescription());
        self::assertSame($throwOnError, $subject->throwOnError());
        self::assertSame("ROOT", $subject->getPathAsString(true));
        self::assertSame("ROOT", $subject->getPathAsString(false));
        self::assertSame([$root, $subject], $subject->getSubjectChain(true));
        self::assertSame([$root], $subject->getSubjectChain(false));
    }

    public function testPathString(): void
    {
        $root = Base\Subject::default(null, 'ROOT');
        $result1 = new Result($root, null, [], '');
        $resultLast = new Result($result1, null, [], '');

        self::assertSame('ROOT ↣', $resultLast->getPathAsString(true));
    }
}
