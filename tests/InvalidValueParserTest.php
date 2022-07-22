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

namespace Philiagus\Parser\Test;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

trait InvalidValueParserTest
{

    abstract public function provideInvalidValuesAndParsers(): array;

    abstract public function expectException(string $exception): void;

    abstract public function expectExceptionMessage(string $message): void;

    /**
     * @param $value
     * @param \Closure $parser
     * @param string|\Closure $expectedException
     *
     * @dataProvider provideInvalidValuesAndParsers
     */
    public function testThatItBlocksInvalidValues(
        $value,
        \Closure $parser,
        string|\Closure $expectedException = ParsingException::class,
        bool $throw = true
    ): void
    {
        $resultValue = null;
        try {
            /** @var Result $result */
            $result = $parser($value)->parse(Subject::default($value, $throw));
        } catch (\Throwable $exception) {

        }
        if (!isset($exception)) {
            $resultValue = $result->getValue();
            self::fail('No exception was thrown and parser for ' . Debug::stringify($value) . ' resulted in: ' . Debug::stringify($resultValue));
        }
        if (is_string($expectedException)) {
            self::assertInstanceOf($expectedException, $exception, "Exception of type $expectedException not thrown");
        } elseif ($expectedException instanceof \Closure) {
            $expectedException($exception);
        } else {
            self::fail('$expectedException must be string|\Closure, ' . Debug::stringify($expectedException) . ' provided');
        }
    }

    /**
     * @param $value
     * @param \Closure $parser
     * @param string|\Closure $expectedException
     *
     * @dataProvider provideInvalidValuesAndParsers
     */
    public function testThatItBlocksInvalidValuesNotThrowing(
        $value,
        \Closure $parser,
        string|\Closure $expectedException = ParsingException::class
    ): void
    {
        /** @var Result $result */
        $result = $parser($value)->parse(Subject::default($value, false));
        self::assertFalse($result->isSuccess());
        self::assertNotEmpty($result->getErrors());
        foreach ($result->getErrors() as $error) {
            self::assertInstanceOf(Error::class, $error);
        }
    }


}
