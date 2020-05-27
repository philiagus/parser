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

namespace Philiagus\Test\Parser\Unit\Parser;

use Philiagus\Parser\Parser\AssertSame;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Test\Parser\Provider\DataProvider;
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
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideEverythingExceptNAN(): array
    {
        return DataProvider::provide((int) ~DataProvider::TYPE_NAN);
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
        self::assertEquals(
            serialize((new AssertSame())->setValue($value, $message)),
            serialize(AssertSame::value($value, $message))
        );
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
        DataProvider::assertSame($value, (new AssertSame())->setValue($value)->parse($value));
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatItDoesNotAcceptsOverwrites(): void
    {
        $parser = AssertSame::new()->setValue('a');
        self::expectException(ParserConfigurationException::class);
        $parser->setValue('b');
    }

}