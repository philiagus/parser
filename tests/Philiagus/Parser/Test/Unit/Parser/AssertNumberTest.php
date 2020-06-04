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
use Philiagus\Parser\Parser\AssertNumber;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertNumberTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertNumber()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int) ~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT));
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideInvalidValues
     */
    public function testThatNonFloatsAreBlocked($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertNumber())->parse($value);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatTypeExceptionMessageIsThrown(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new AssertNumber())->overwriteTypeExceptionMessage($msg)->parse('yes');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_FLOAT | DataProvider::TYPE_INTEGER);
    }

    /**
     * @param mixed $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideValidValues
     */
    public function testThatFloatsAreAllowed($value): void
    {
        $result = (new AssertNumber())->parse($value);
        self::assertSame($result, $value);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueMustBeGreaterThanMinimum(): void
    {
        $this->expectException(ParsingException::class);
        (new AssertNumber())
            ->withMinimum(10.0)
            ->parse(0.0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueMustBeLowerThanMaximum(): void
    {
        $this->expectException(ParsingException::class);
        (new AssertNumber())
            ->withMaximum(0.0)
            ->parse(10.0);
    }

    /**
     * @return array
     */
    public function provideOutOfRangeValues(): array
    {
        return [
            'lower' => [-1.0],
            'upper' => [11.0],
        ];
    }

    /**
     * @param $value
     *
     * @dataProvider provideInvalidValues
     *
     * @throws ParserConfigurationException
     */
    public function testThatMinimumDisallowsNonFloatOrInt($value): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertNumber())->withMinimum($value);
    }

    /**
     * @param $value
     *
     * @dataProvider provideValidValues
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatMinimumAllowsFloatOrInt($value): void
    {
        self::assertSame($value, (new AssertNumber())->withMinimum($value)->parse($value));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueOverMinimumPasses(): void
    {
        $parser = (new AssertNumber())->withMinimum(0.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(0.0, $parser->parse(0.0));
        self::assertSame(PHP_INT_MAX, $parser->parse(PHP_INT_MAX));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueUnderMaximumPasses(): void
    {
        $parser = (new AssertNumber())->withMaximum(10.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(1.0, $parser->parse(1.0));
        self::assertSame(PHP_INT_MIN, $parser->parse(PHP_INT_MIN));
    }


    /**
     * @param $value
     *
     * @dataProvider provideInvalidValues
     *
     * @throws ParserConfigurationException
     */
    public function testThatMaximumDisallowsNonFloatOrInt($value): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertNumber())->withMaximum($value);
    }

    /**
     * @param $value
     *
     * @dataProvider provideValidValues
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatMaximumAllowsFloatOrInt($value): void
    {
        self::assertSame($value, (new AssertNumber())->withMaximum($value)->parse($value));
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
     * @param float|int $value
     * @param float|int $min
     *
     * @dataProvider provideMinExceptionTestMessages
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatMinExceptionMessageIsUsed(string $base, string $expectedMsg, $value, $min): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expectedMsg);
        (new AssertNumber())->withMinimum($min, $base)->parse($value);
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
     * @param float|int $value
     * @param float|int $max
     *
     * @dataProvider provideMaxExceptionTestMessages
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatMaxExceptionMessageIsUsed(string $base, string $expectedMsg, $value, $max): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expectedMsg);
        (new AssertNumber())->withMaximum($max, $base)->parse($value);
    }

    public function testAllOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'hello string string<ASCII>(5)"hello"'
        );
        (new AssertNumber())
            ->overwriteTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse('hello');
    }

    public function testAllWithMinimumMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '0 integer integer 0 | 10 integer integer 10'
        );
        (new AssertNumber())
            ->withMinimum(10, '{value} {value.type} {value.debug} | {min} {min.type} {min.debug}')
            ->parse(0);
    }

    public function testAllWithMaximumMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '100 integer integer 100 | 10 integer integer 10'
        );
        (new AssertNumber())
            ->withMaximum(10, '{value} {value.type} {value.debug} | {max} {max.type} {max.debug}')
            ->parse(100);
    }

}