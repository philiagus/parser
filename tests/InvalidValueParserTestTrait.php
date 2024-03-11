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

use Closure;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Stringify;

trait InvalidValueParserTestTrait
{

    abstract public static function provideInvalidValuesAndParsers(): array;

    abstract public function expectException(string $exception): void;

    abstract public function expectExceptionMessage(string $message): void;

    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvalidValuesAndParsers')]
    public function testThatItBlocksInvalidValues(
        $value,
        \Closure $parser,
        string|\Closure $expectedException = ParsingException::class,
        bool $throw = true
    ): void
    {
        $parserInstance = $parser($value);
        for($repeat = 2;$repeat > 0;$repeat--) {
            try {
                /** @var Contract\Result $result */
                $result = $parserInstance->parse(Subject::default($value, throwOnError: $throw));
            } catch (\Throwable $exception) {

            }
            if (!isset($exception)) {
                $resultValue = $result->getValue();
                self::fail('No exception was thrown and parser for ' . Stringify::stringify($value) . ' resulted in: ' . Stringify::stringify($resultValue));
            }
            if (is_string($expectedException)) {
                self::assertInstanceOf($expectedException, $exception, "Exception of type $expectedException not thrown: " . (string)$exception);
            } elseif ($expectedException instanceof \Closure) {
                $expectedException($exception);
            } else {
                self::fail('$expectedException must be string|\Closure, ' . Stringify::stringify($expectedException) . ' provided');
            }
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvalidValuesAndParsers')]
    public function testThatItBlocksInvalidValuesNotThrowing(
        $value,
        \Closure $parser
    ): void
    {
        $parserInstance = $parser($value);
        for($repeat = 2;$repeat > 0;$repeat--) {
            /** @var Contract\Result $result */
            $result = $parserInstance->parse(Subject::default($value, throwOnError: false));
            self::assertFalse($result->isSuccess());
            self::assertNotEmpty($result->getErrors());
            foreach ($result->getErrors() as $error) {
                self::assertInstanceOf(Error::class, $error);
            }
        }
    }


}
