<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Philiagus\Test\Parser\Unit\Parser;

use Exception;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertNumeric;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AssertNumericTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertNumeric()) instanceof Parser);
    }

    /**
     * @return array
     * @throws Exception
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
        (new AssertNumeric())->parse($value);
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
        (new AssertNumeric())->withTypeExceptionMessage($msg)->parse('yes');
    }

    /**
     * @return array
     * @throws Exception
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
        $result = (new AssertNumeric())->parse($value);
        self::assertSame($result, $value);
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatMinimumCannotBeGreaterThanMaximum(): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertNumeric())->withMaximum(100)->withMinimum(1000);
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatMaximumCannotBeLowerThanMinimum(): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertNumeric())->withMinimum(1000.0)->withMaximum(100.0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueMustBeGreaterThanMinimum(): void
    {
        $this->expectException(ParsingException::class);
        (new AssertNumeric())
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
        (new AssertNumeric())
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
     * @dataProvider provideInvalidValues
     *
     * @throws ParserConfigurationException
     */
    public function testThatMinimumDisallowsNonFloatOrInt($value): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertNumeric())->withMinimum($value);
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
        self::assertSame($value, (new AssertNumeric())->withMinimum($value)->parse($value));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueOverMinimumPasses(): void
    {
        $parser = (new AssertNumeric())->withMinimum(0.0);
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
        $parser = (new AssertNumeric())->withMaximum(10.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(1.0, $parser->parse(1.0));
        self::assertSame(PHP_INT_MIN, $parser->parse(PHP_INT_MIN));
    }



    /**
     * @param $value
     * @dataProvider provideInvalidValues
     *
     * @throws ParserConfigurationException
     */
    public function testThatMaximumDisallowsNonFloatOrInt($value): void
    {
        self::expectException(ParserConfigurationException::class);
        (new AssertNumeric())->withMaximum($value);
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
        self::assertSame($value, (new AssertNumeric())->withMaximum($value)->parse($value));
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
        (new AssertNumeric())->withMinimum($min, $base)->parse($value);
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
        (new AssertNumeric())->withMaximum($max, $base)->parse($value);
    }

}