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

use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\Root;
use Philiagus\Parser\Subject\Utility\Internal;
use Philiagus\Parser\Subject\Utility\ParserBegin;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Util\Debug;

/**
 * @covers \Philiagus\Parser\ResultBuilder
 */
class ResultBuilderTest extends TestBase
{

    public function testFull(): void
    {
        $value0 = new \stdClass();
        $subject0 = new Root($value0);
        $builder = new ResultBuilder($subject0, 'description');
        self::assertEquals($builder, $subject0->getResultBuilder('description'));
        $subject1 = $builder->getSubject();
        self::assertInstanceOf(ParserBegin::class, $subject1);
        self::assertSame('description', $subject1->getDescription());
        self::assertSame($subject0, $subject1->getSourceSubject());
        self::assertSame($value0, $builder->getValue());
        self::assertFalse($builder->hasErrors());
        $value1 = new \stdClass();
        $builder->setValue('internal', $value1);

        $subject2 = $builder->getSubject();
        self::assertInstanceOf(Internal::class, $subject2);
        self::assertSame($value1, $builder->getValue());
        self::assertSame($value1, $subject2->getValue());
        self::assertSame($subject1, $subject2->getSourceSubject());
        self::assertFalse($builder->hasErrors());

        $value2 = new \stdClass();

        $result0 = $builder->createResultUnchanged();
        self::assertTrue($result0->isSuccess());
        self::assertFalse($result0->hasErrors());
        self::assertSame($value0, $result0->getValue());

        $result1 = $builder->createResultWithCurrentValue();
        self::assertTrue($result1->isSuccess());
        self::assertFalse($result1->hasErrors());
        self::assertSame($value1, $result1->getValue());

        $result2 = $builder->createResult($value2);
        self::assertTrue($result2->isSuccess());
        self::assertFalse($result2->hasErrors());
        self::assertSame($value2, $result2->getValue());
    }

    public function testLogErrorWithoutThrow(): void
    {
        $subject = new Root(null, throwOnError: false);
        $builder = $subject->getResultBuilder('');

        self::assertFalse($builder->hasErrors());

        $builder->logError(
            $error1 = new Error($subject, '1')
        );

        self::assertTrue($builder->hasErrors());

        $builder->logError(
            $error2 = new Error($subject, '2')
        );

        $result = $builder->createResultUnchanged();
        self::assertFalse($result->isSuccess());
        self::assertTrue($result->hasErrors());
        self::assertSame([$error1, $error2], $result->getErrors());
    }

    public function testLogErrorWithThrow(): void
    {
        $subject = new Root(null);
        $builder = $subject->getResultBuilder('');

        self::assertFalse($builder->hasErrors());

        self::expectException(ParsingException::class);
        self::expectExceptionMessage('1');
        $error = new Error($subject, '1');
        try {
            $builder->logError($error);
        } catch (ParsingException $e) {
            self::assertSame($error, $e->getError());
            throw $e;
        }
    }

    public function testLogErrorUsingDebugWithoutThrow()
    {

        $subject = new Root(null, throwOnError: false);
        $builder = $subject->getResultBuilder('');
        $object = new \stdClass();

        self::assertFalse($builder->hasErrors());

        $sourceThrowable = new \Exception();
        $sourceErrors = [
            new Error($subject, ''),
        ];

        $builder->logErrorUsingDebug(
            'message1 {subject.debug} {a.debug}',
            ['a' => $object],
            $sourceThrowable,
            $sourceErrors
        );

        self::assertTrue($builder->hasErrors());

        $builder->logErrorUsingDebug(
            'message2 {subject.debug} {a.debug}',
            ['a' => $object],
            $sourceThrowable,
            $sourceErrors
        );

        $result = $builder->createResultUnchanged();
        self::assertFalse($result->isSuccess());
        self::assertTrue($result->hasErrors());
        self::assertCount(2, $result->getErrors());
        [$error1, $error2] = $result->getErrors();
        self::assertSame(
            Debug::parseMessage(
                'message1 {subject.debug} {a.debug}',
                [
                    'subject' => $subject->getValue(),
                    'a' => $object,
                ]
            ),
            $error1->getMessage()
        );
        self::assertSame(
            Debug::parseMessage(
                'message2 {subject.debug} {a.debug}',
                [
                    'subject' => $subject->getValue(),
                    'a' => $object,
                ]
            ),
            $error2->getMessage()
        );
    }

    public function testLogErrorUsingDebugWithThrow()
    {

        $subject = new Root(null, throwOnError: true);
        $builder = $subject->getResultBuilder('');
        $object = new \stdClass();

        self::assertFalse($builder->hasErrors());

        $sourceThrowable = new \Exception();
        $sourceErrors = [
            new Error($subject, ''),
        ];
        $expectedMessage = Debug::parseMessage(
            'message1 {subject.debug} {a.debug}',
            [
                'subject' => $subject->getValue(),
                'a' => $object,
            ]
        );
        self::expectException(ParsingException::class);
        self::expectExceptionMessage($expectedMessage);
        try {
            $builder->logErrorUsingDebug(
                'message1 {subject.debug} {a.debug}',
                ['a' => $object],
                $sourceThrowable,
                $sourceErrors
            );
        } catch (ParsingException $e) {
            $error = $e->getError();
            self::assertSame(
                $expectedMessage,
                $error->getMessage()
            );

            throw $e;
        }
    }

    public function testIncorporateResult_Success(): void
    {
        $value = new \stdClass();
        $subject = new Root(null);
        $builder = $subject->getResultBuilder('');
        self::assertSame($value, $builder->incorporateResult(
            new Result($subject, $value, [])
        ));
        self::assertFalse($builder->hasErrors());
    }

    public function testIncorporateResult_ErrorNoThrow(): void
    {
        $value = new \stdClass();
        $subject = new Root(null, throwOnError: false);
        $builder = $subject->getResultBuilder('');
        self::assertSame($value, $builder->incorporateResult(
            new Result($subject, false, [
                $error = new Error($subject, 'ERROR'),
            ]), $value
        ));
        self::assertTrue($builder->hasErrors());
        $result = $builder->createResultUnchanged();
        self::assertSame([$error], $result->getErrors());
    }

    public function testIncorporateResult_ErrorThrow(): void
    {
        $subject = new Root(null, throwOnError: true);
        $builder = $subject->getResultBuilder('');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('ERROR1');
        $builder->incorporateResult(
            new Result($subject, false, [
                new Error($subject, 'ERROR1'),
                new Error($subject, 'ERROR2'),
            ])
        );
    }


    public function testCreateResultFromResult_Success(): void
    {
        $value = new \stdClass();
        $subject = new Root(null);
        $builder = $subject->getResultBuilder('');
        $result = $builder->createResultFromResult(
            new Result($subject, $value, [])
        );
        self::assertFalse($result->hasErrors());
        self::assertFalse($builder->hasErrors());
        self::assertSame($value, $result->getValue());
    }

    public function testCreateResultFromResult_ErrorNoThrow(): void
    {
        $value = new \stdClass();
        $subject = new Root(null, throwOnError: false);
        $builder = $subject->getResultBuilder('');
        $builder->logError(
            $error0 = new Error($subject, 'FIRST')
        );
        $result = $builder->createResultFromResult(
            new Result($subject, false, [
                $error1 = new Error($subject, 'ERROR'),
            ])
        );
        self::assertTrue($result->hasErrors());
        self::assertSame([$error0, $error1], $result->getErrors());
    }

    public function testCreateResultFromResult_ErrorThrow(): void
    {
        $subject = new Root(null, throwOnError: true);
        $builder = $subject->getResultBuilder('');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('ERROR1');
        $builder->createResultFromResult(
            new Result($subject, false, [
                new Error($subject, 'ERROR1'),
                new Error($subject, 'ERROR2'),
            ])
        );
    }

}
