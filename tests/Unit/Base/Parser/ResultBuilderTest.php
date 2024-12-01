<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Base\Parser;

use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject\Memory;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Root;
use Philiagus\Parser\Subject\Utility\Internal;
use Philiagus\Parser\Subject\Utility\ParserBegin;
use Philiagus\Parser\Test\Mock\SubjectMock;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Util\Stringify;
use PHPUnit\Framework\Attributes\CoversClass;
use Prophecy\Argument;

#[CoversClass(ResultBuilder::class)]
class ResultBuilderTest extends TestBase
{

    public function testFull(): void
    {
        $value0 = new \stdClass();
        $subject0 = new Root($value0);
        $builder = new ResultBuilder($subject0, 'description');
        $subject1 = $builder->getSubject();
        self::assertInstanceOf(ParserBegin::class, $subject1);
        self::assertSame('description', $subject1->getDescription());
        self::assertSame($subject0, $subject1->getSource());
        self::assertSame($value0, $builder->getValue());
        self::assertFalse($builder->hasErrors());
        $value1 = new \stdClass();
        $builder->setValue('internal', $value1);

        $subject2 = $builder->getSubject();
        self::assertInstanceOf(Internal::class, $subject2);
        self::assertSame($value1, $builder->getValue());
        self::assertSame($value1, $subject2->getValue());
        self::assertSame($subject1, $subject2->getSource());
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
        $builder = new ResultBuilder($subject, '');

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
        $builder = new ResultBuilder($subject, '');

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

    public function testLogErrorStringifyWithoutThrow()
    {

        $subject = new Root(null, throwOnError: false);
        $builder = new ResultBuilder($subject, '');
        $object = new \stdClass();

        self::assertFalse($builder->hasErrors());

        $sourceThrowable = new \Exception();
        $sourceErrors = [
            new Error($subject, ''),
        ];

        $builder->logErrorStringify(
            'message1 {value.debug} {a.debug}',
            ['a' => $object],
            $sourceThrowable,
            $sourceErrors
        );

        self::assertTrue($builder->hasErrors());

        $builder->logErrorStringify(
            'message2 {value.debug} {a.debug}',
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
            Stringify::parseMessage(
                'message1 {value.debug} {a.debug}',
                [
                    'value' => $subject->getValue(),
                    'a' => $object,
                ]
            ),
            $error1->getMessage()
        );
        self::assertSame(
            Stringify::parseMessage(
                'message2 {value.debug} {a.debug}',
                [
                    'value' => $subject->getValue(),
                    'a' => $object,
                ]
            ),
            $error2->getMessage()
        );
    }

    public function testLogErrorStringifyWithThrow()
    {

        $subject = new Root(null, throwOnError: true);
        $builder = new ResultBuilder($subject, '');
        $object = new \stdClass();

        self::assertFalse($builder->hasErrors());

        $sourceThrowable = new \Exception();
        $sourceErrors = [
            new Error($subject, ''),
        ];
        $expectedMessage = Stringify::parseMessage(
            'message1 {value.debug} {a.debug}',
            [
                'value' => $subject->getValue(),
                'a' => $object,
            ]
        );
        self::expectException(ParsingException::class);
        self::expectExceptionMessage($expectedMessage);
        try {
            $builder->logErrorStringify(
                'message1 {value.debug} {a.debug}',
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
        $builder = new ResultBuilder($subject, '');
        self::assertSame($value, $builder->unwrapResult(
            new Result($subject, $value, [])
        ));
        self::assertFalse($builder->hasErrors());
    }

    public function testIncorporateResult_ErrorNoThrow(): void
    {
        $value = new \stdClass();
        $subject = new Root(null, throwOnError: false);
        $builder = new ResultBuilder($subject, '');
        self::assertSame($value, $builder->unwrapResult(
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
        $builder = new ResultBuilder($subject, '');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('ERROR1');
        $builder->unwrapResult(
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
        $builder = new ResultBuilder($subject, '');
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
        $builder = new ResultBuilder($subject, '');
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
        $builder = new ResultBuilder($subject, '');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('ERROR1');
        $builder->createResultFromResult(
            new Result($subject, false, [
                new Error($subject, 'ERROR1'),
                new Error($subject, 'ERROR2'),
            ])
        );
    }

    public function testMemoryMethods(): void
    {
        $object = new \stdClass();
        $default = new \stdClass();
        $memory = $this->prophesize(Memory::class);
        $memory->set($object, 'yes')->shouldBeCalledOnce();
        $memory->has($object)->shouldBeCalledOnce()->willReturn(true);
        $memory->get($object, Argument::is($default))->shouldBeCalledOnce()->willReturn($default);
        $memory->get($object, null)->shouldBeCalledOnce()->willReturn('OINK');
        $memory = $memory->reveal();
        $subject = new SubjectMock(
            source: new SubjectMock(
                fullMemory: $memory
            )
        );

        $builder = new ResultBuilder($subject, '');
        self::assertTrue($builder->hasMemory($object));
        self::assertSame($default, $builder->getMemory($object, $default));
        self::assertSame('OINK', $builder->getMemory($object));
        $builder->setMemory($object, 'yes');
        self::assertSame($memory, $builder->getFullMemory());
    }

}
