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

namespace Philiagus\Parser\Test\Unit\Exception;

use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Test\Mock\ErrorMock;
use Philiagus\Parser\Test\Mock\SubjectMock;
use Philiagus\Parser\Test\TestBase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ParsingException::class)]
class ParsingExceptionTest extends TestBase
{
    public function testGetError(): void
    {
        $error = new ErrorMock(
            sourceThrowable: null,
            message: 'the message',
        );

        $exception = new ParsingException($error);
        self::assertSame($error, $exception->getError());
    }

    public function testThrowable(): void
    {
        $error = new ErrorMock(
            message: 'the message',
            sourceThrowable: null,
        );

        $exception = new ParsingException($error);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    public function testGetSubject(): void
    {
        $subject = new SubjectMock();

        $error = new ErrorMock(
            subject: $subject,
            message: 'the message',
            sourceThrowable: null,
        );

        $exception = new ParsingException($error);
        self::assertSame($subject, $exception->getSubject());
    }

    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $includeUtility
     *
     * @return void
     */
    public function testGetPathAsString(bool $includeUtility): void
    {
        $expected = uniqid(microtime());
        $subject = new SubjectMock(isUtility: false, path: $expected);

        $error = new ErrorMock(
            subject: $subject,
            message: 'the message',
            sourceThrowable: null,
        );

        $exception = new ParsingException($error);
        self::assertSame($expected, $exception->getPathAsString($includeUtility));
    }
}
