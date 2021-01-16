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
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Fixed;
use PHPUnit\Framework\TestCase;

class FixedTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new Fixed()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideAllTypes(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))->provide();
    }

    /**
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideAllTypes
     */
    public function testThatItIgnoresAnyInputAndReturnsTheDefinedValue($value): void
    {
        $instance = new \stdClass();
        self::assertSame($instance, (new Fixed())->setValue($instance)->parse($value));
    }

    /**
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideAllTypes
     */
    public function testThatItAcceptsAnyValueAsFixed($value): void
    {
        $instance = new \stdClass();
        $result = (new Fixed())->setValue($value)->parse($instance);
        self::assertTrue(DataProvider::isSame($value, $result));
    }

    /**
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideAllTypes
     */
    public function testStaticValue($value): void
    {
        $instance = new \stdClass();
        $result = Fixed::value($value)->parse($instance);
        self::assertTrue(DataProvider::isSame($value, $result));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItThrowsAnExceptionIfNoValueIsDefined(): void
    {
        $this->expectException(ParserConfigurationException::class);
        (new Fixed())->parse(null);
    }

    /**
     * @throws ParserConfigurationException
     */
    public function testThatValueCannotBeOverwritten(): void
    {
        $instance = Fixed::new()->setValue('asdf');
        $this->expectException(ParserConfigurationException::class);
        $instance->setValue('b');
    }

}