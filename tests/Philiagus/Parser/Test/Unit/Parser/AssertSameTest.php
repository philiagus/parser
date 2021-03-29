<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Parser\AssertSame;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use PHPUnit\Framework\TestCase;

class AssertSameTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertSame()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideEverything(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))->provide();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideEverythingExceptNAN(): array
    {
        return (new DataProvider(~DataProvider::TYPE_NAN))->provide();
    }

    /**
     * @return array
     */
    public function notZeroValues(): array
    {
        return [
            '0.0' => [0.0],
            '1' => [1],
            'empty string' => [''],
            'null' => [null],
        ];
    }

    /**
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider notZeroValues
     */
    public function testThatItBlocksNotSameValue($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertSame())->setValue(0)->parse($value);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItAllowsSameValue(): void
    {
        $parser = (new AssertSame())->setValue(0);
        self::assertSame(0, $parser->parse(0));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testExceptionOnMissingConfiguration(): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new AssertSame())->parse(0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new AssertSame())->setValue(1, $msg)->parse('1');
    }

    /**
     * @dataProvider provideEverything
     *
     * @param $value
     */
    public function testStaticConstructor($value): void
    {
        $message = 'hello';
        self::assertTrue(DataProvider::isEqual(
            (new AssertSame())->setValue($value, $message),
            AssertSame::value($value, $message)
        ));
    }

    /**
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideEverythingExceptNAN
     */
    public function testThatItAcceptsAllValues($value): void
    {
        self::assertTrue(DataProvider::isSame($value, (new AssertSame())->setValue($value)->parse($value)));
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatItDoesAcceptsOverwrites(): void
    {
        $parser = AssertSame::new()->setValue('a');
        $parser->setValue('b');
        self::assertSame('b', $parser->parse('b'));
    }

    public function testAllSetTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            'hello string string<ASCII>(5)"hello" | 6 integer integer 6'
        );
        (new AssertSame())
            ->setValue(6, '{value} {value.type} {value.debug} | {expected} {expected.type} {expected.debug}')
            ->parse('hello');
    }

}