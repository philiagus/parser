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
use Philiagus\Parser\Test\Mock\SubjectMock;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Util\Stringify;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Error::class)]
class ErrorTest extends TestBase
{

    /**
     * @testWith [true, true]
     *           [true, false]
     *           [false, true]
     *           [false, false]
     */
    public function test(bool $hasSourceThrowable, bool $hasSourceErrors): void
    {
        $createError = function () {
            $subject = new SubjectMock();

            return new Error($subject, 'MESSAGE');
        };

        if ($hasSourceErrors) {
            $prevErrors = [$createError(), $createError(), $createError()];
        } else {
            $prevErrors = [];
        }

        $source = $hasSourceThrowable ? new \Exception() : null;
        $subject = new SubjectMock();
        $error1 = new Error(
            $subject,
            $message = Stringify::parseMessage(
                'REP {value.debug} {further.debug}',
                ['value' => $subject->getValue(), 'further' => 'OII']
            ),
            $source,
            $prevErrors
        );
        $error2 = Error::createUsingStringify(
            $subject,
            'REP {value.debug} {further.debug}',
            ['further' => 'OII'],
            $source,
            $prevErrors
        );

        self::assertEquals($error1, $error2);
        self::assertSame($subject, $error1->getSubject());
        self::assertSame($subject->getPathAsString(true), $error1->getPathAsString(true));
        self::assertSame($subject->getPathAsString(false), $error1->getPathAsString(false));
        self::assertSame($source, $error1->getSourceThrowable());
        self::assertSame($prevErrors, $error1->getSourceErrors());
        self::assertSame($hasSourceErrors, $error1->hasSourceErrors());
        self::assertSame($hasSourceThrowable, $error1->hasSourceThrowable());
        self::assertSame($message, $error1->getMessage());

        self::expectException(ParsingException::class);
        try {
            $error1->throw();
        } catch (ParsingException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($error1, $e->getError());
            throw $e;
        }
    }

    public function testExceptionOnNonError(): void
    {
        $subject = new SubjectMock();
        self::expectException(\LogicException::class);
        new Error($subject, 'msg', sourceErrors: ['lol']);
    }

}
