<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Philiagus\Parser\Test\Unit\Parser;


use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertIntegerTest extends TestCase
{
    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertInteger()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int) ~DataProvider::TYPE_INTEGER);
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideInvalidValues
     */
    public function testThatNonIntegersAreBlocked($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertInteger())->parse($value);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_INTEGER);
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidValues
     */
    public function testThatIntegersAreAllowed($value): void
    {
        $result = (new AssertInteger())->parse($value);
        self::assertSame($result, $value);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueMustBeGreaterThanMinimum(): void
    {
        $this->expectException(ParsingException::class);
        (new AssertInteger())
            ->withMinimum(10)
            ->parse(0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueMustBeLowerThanMaximum(): void
    {
        $this->expectException(ParsingException::class);
        (new AssertInteger())
            ->withMaximum(0)
            ->parse(10);
    }

    /**
     * @return array
     */
    public function provideOutOfRangeValues(): array
    {
        return [
            'lower' => [-1],
            'upper' => [11],
        ];
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueOverMinimumPasses(): void
    {
        $parser = (new AssertInteger())->withMinimum(0);
        self::assertSame(10, $parser->parse(10));
        self::assertSame(0, $parser->parse(0));
        self::assertSame(PHP_INT_MAX, $parser->parse(PHP_INT_MAX));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueUnderMaximumPasses(): void
    {
        $parser = (new AssertInteger())->withMaximum(10);
        self::assertSame(10, $parser->parse(10));
        self::assertSame(1, $parser->parse(1));
        self::assertSame(PHP_INT_MIN, $parser->parse(PHP_INT_MIN));
    }

    public function provideValidMultipleOfs(): array
    {
        return [
            '10 is multiple of 5' => [10, 5],
            '-10 is multiple of -5' => [10, 5],
        ];
    }

    /**
     * @param int $value
     * @param int $base
     *
     * @dataProvider provideValidMultipleOfs
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testIsMultipleOf(int $value, int $base): void
    {
        self::assertSame(
            $value,
            (new AssertInteger())
                ->withMultipleOf($base)
                ->parse($value)
        );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testisMultipleOfWrongValueException(): void
    {
        $this->expectException(ParsingException::class);
        (new AssertInteger())->withMultipleOf(10)->parse(9);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testWithTypeExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new AssertInteger())->overwriteTypeExceptionMessage($msg)->parse(false);
    }

    public function provideMinExceptionTestMessages(): array
    {
        return [
            'no replaces' => ['this is the message', 'this is the message', 10, 11],
            'min replace' => ['minimum {min} was expected', 'minimum 11 was expected', 10, 11],
            'min & value replace' => ['min {min} value {value}', 'min 11 value 10', 10, 11],
            'value replace' => ['value {value}', 'value 10', 10, 11],
        ];
    }

    /**
     * @param string $base
     * @param string $expectedMsg
     * @param int $value
     * @param int $min
     *
     * @dataProvider provideMinExceptionTestMessages
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatMinExceptionMessageIsUsed(string $base, string $expectedMsg, int $value, int $min): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expectedMsg);
        (new AssertInteger())->withMinimum($min, $base)->parse($value);
    }

    public function provideMaxExceptionTestMessages(): array
    {
        return [
            'no replaces' => ['this is the message', 'this is the message', 16, 11],
            'max replace' => ['max {max} was expected', 'max 11 was expected', 16, 11],
            'max & value replace' => ['max {max} value {value}', 'max 11 value 16', 16, 11],
            'value replace' => ['value {value}', 'value 16', 16, 11],
        ];
    }

    /**
     * @param string $base
     * @param string $expectedMsg
     * @param int $value
     * @param int $max
     *
     * @dataProvider provideMaxExceptionTestMessages
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatMaxExceptionMessageIsUsed(string $base, string $expectedMsg, int $value, int $max): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expectedMsg);
        (new AssertInteger())->withMaximum($max, $base)->parse($value);
    }

    public function provideBaseExceptionTestMessages(): array
    {
        return [
            'no replaces' => ['this is the message', 'this is the message', 10, 4],
            'base replace' => ['base {base} was expected', 'base 4 was expected', 10, 4],
            'base & value replace' => ['base {base} value {value}', 'base 4 value 10', 10, 4],
            'value replace' => ['value {value}', 'value 10', 10, 4],
        ];
    }

    /**
     * @param string $baseMsg
     * @param string $expectedMsg
     * @param int $value
     * @param int $base
     *
     * @dataProvider provideBaseExceptionTestMessages
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testIsMultipleOfExceptionMessageIsUsed(string $baseMsg, string $expectedMsg, int $value, int $base): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expectedMsg);
        (new AssertInteger())->withMultipleOf($base, $baseMsg)->parse($value);
    }

    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'hello string string<ASCII>(5)"hello"'
        );
        (new AssertInteger())
            ->overwriteTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse('hello');
    }

    public function testAllWithMinimumMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '0 integer integer 0 | 10 integer integer 10'
        );
        (new AssertInteger())
            ->withMinimum(10, '{value} {value.type} {value.debug} | {min} {min.type} {min.debug}')
            ->parse(0);
    }

    public function testAllWithMaximumMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '100 integer integer 100 | 10 integer integer 10'
        );
        (new AssertInteger())
            ->withMaximum(10, '{value} {value.type} {value.debug} | {max} {max.type} {max.debug}')
            ->parse(100);
    }

    public function testAllWithMultipleOfMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '2 integer integer 2 | 10 integer integer 10'
        );
        (new AssertInteger())
            ->withMultipleOf(10, '{value} {value.type} {value.debug} | {base} {base.type} {base.debug}')
            ->parse(2);
    }

}