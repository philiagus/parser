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

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertFloat;
use PHPUnit\Framework\TestCase;

class AssertFloatTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertFloat()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return (new DataProvider(~DataProvider::TYPE_FLOAT))->provide();
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
        (new AssertFloat())->parse($value);
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
        (new AssertFloat())->setTypeExceptionMessage($msg)->parse('yes');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return (new DataProvider(DataProvider::TYPE_FLOAT))->provide();
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
        $result = (new AssertFloat())->parse($value);
        self::assertSame($result, $value);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueMustBeGreaterThanMinimum(): void
    {
        $this->expectException(ParsingException::class);
        (new AssertFloat())
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
        (new AssertFloat())
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
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueOverMinimumPasses(): void
    {
        $parser = (new AssertFloat())->withMinimum(0.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(0.0, $parser->parse(0.0));
        self::assertSame((float) PHP_INT_MAX, $parser->parse((float) PHP_INT_MAX));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatValueUnderMaximumPasses(): void
    {
        $parser = (new AssertFloat())->withMaximum(10.0);
        self::assertSame(10.0, $parser->parse(10.0));
        self::assertSame(1.0, $parser->parse(1.0));
        self::assertSame((float) PHP_INT_MIN, $parser->parse((float) PHP_INT_MIN));
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
     * @param float $value
     * @param float $min
     *
     * @dataProvider provideMinExceptionTestMessages
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatMinExceptionMessageIsUsed(string $base, string $expectedMsg, float $value, float $min): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expectedMsg);
        (new AssertFloat())->withMinimum($min, $base)->parse($value);
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
     * @param float $value
     * @param float $max
     *
     * @dataProvider provideMaxExceptionTestMessages
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatMaxExceptionMessageIsUsed(string $base, string $expectedMsg, float $value, float $max): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expectedMsg);
        (new AssertFloat())->withMaximum($max, $base)->parse($value);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideNANAndINF(): array
    {
        return (new DataProvider(DataProvider::TYPE_NAN | DataProvider::TYPE_INFINITE))->provide();
    }

    /**
     * @param $value
     *
     * @dataProvider provideNANAndINF
     * @throws ParserConfigurationException
     */
    public function testThatMinimumDisallowsNANAndINF($value): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertFloat())->withMinimum($value);
    }

    /**
     * @param $value
     *
     * @dataProvider provideNANAndINF
     * @throws ParserConfigurationException
     */
    public function testThatMaximumDisallowsNANAndINF($value): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertFloat())->withMaximum($value);
    }

    public function testAllSetTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'hello string string<ASCII>(5)"hello"'
        );
        (new AssertFloat())
            ->setTypeExceptionMessage('{value} {value.type} {value.debug}')
            ->parse('hello');
    }

    public function testAllWithMinimumMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '0 float float 0 | 10 float float 10'
        );
        (new AssertFloat())
            ->withMinimum(10, '{value} {value.type} {value.debug} | {min} {min.type} {min.debug}')
            ->parse(0.0);
    }

    public function testAllWithMaximumMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '1000 float float 1000 | 10 float float 10'
        );
        (new AssertFloat())
            ->withMaximum(10, '{value} {value.type} {value.debug} | {max} {max.type} {max.debug}')
            ->parse(1000.0);
    }

}