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

use Philiagus\Parser\Contract;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Test\TestBase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ParsingException::class)]
class ParsingExceptionTest extends TestBase
{
    public function testGetError(): void
    {
        $error = $this->prophesize(Contract\Error::class);
        $error->getSourceThrowable()->willReturn(null);
        $error->getMessage()->willReturn('the message');
        $error = $error->reveal();

        $exception = new ParsingException($error);
        self::assertSame($error, $exception->getError());
    }

    public function testThrowable(): void
    {
        $error = $this->prophesize(Contract\Error::class);
        $error->getSourceThrowable()->willReturn(null);
        $error->getMessage()->willReturn('the message');
        $error = $error->reveal();

        $exception = new ParsingException($error);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    public function testGetSubject(): void
    {
        $subject = $this->prophesize(Contract\Subject::class);
        $subject = $subject->reveal();

        $error = $this->prophesize(Contract\Error::class);
        $error->getSourceThrowable()->willReturn(null);
        $error->getMessage()->willReturn('the message');
        $error->getSubject()->willReturn($subject);
        $error = $error->reveal();

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
        $subject = $this->prophesize(Contract\Subject::class);
        $subject->getPathAsString($includeUtility)->shouldBeCalled()->willReturn($expected);
        $subject = $subject->reveal();

        $error = $this->prophesize(Contract\Error::class);
        $error->getSourceThrowable()->willReturn(null);
        $error->getMessage()->willReturn('the message');
        $error->getSubject()->willReturn($subject);
        $error = $error->reveal();

        $exception = new ParsingException($error);
        self::assertSame($expected, $exception->getPathAsString($includeUtility));
    }
}
