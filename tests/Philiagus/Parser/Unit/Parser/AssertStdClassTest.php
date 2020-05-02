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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertStdClass;
use Philiagus\Parser\Path\Property;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AssertStdClassTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertInstanceOf(Parser::class, new AssertStdClass());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL, function ($element) {
            return !is_object($element) || get_class($element) !== \stdClass::class;
        });
    }

    /**
     * @dataProvider provideInvalidValues
     *
     * @param $value
     *
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testThatItBlocksInvalidValues($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertStdClass())->parse($value);
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testThatItAllowsStdClass(): void
    {
        $object = new \stdClass();
        self::assertSame($object, ((new AssertStdClass())->parse($object)));
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
        (new AssertStdClass())
            ->overwriteTypeExceptionMessage($msg)
            ->parse(1);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItAssertsDefinedProperty(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute(1, Argument::type(Property::class))->shouldBeCalledOnce();
        /** @var Parser $child */
        $child = $parser->reveal();
        (new AssertStdClass())
            ->withProperty('prop', $child)
            ->parse(
                (object) [
                    'prop' => 1,
                ]
            );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItThrowsAnExceptionOnMissingProperty(): void
    {
        $child = new class() extends Parser
        {
            protected function execute($value, Path $path)
            {
            }
        };

        $this->expectException(ParsingException::class);
        (new AssertStdClass())
            ->withProperty('prop', $child)
            ->parse(
                (object) []
            );
    }

    public function providePropertyExceptions(): array
    {
        return [
            'no replace' => ['msg', 'msg', 'prop'],
            'replace property' => ['prop {property}', 'prop \'prop\'', 'prop'],
        ];
    }

    /**
     * @param string $baseMsg
     * @param string $expected
     * @param string $propName
     *
     * @dataProvider providePropertyExceptions
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItRespectsDefinedExceptionMessage(string $baseMsg, string $expected, string $propName): void
    {
        $child = new class() extends Parser
        {
            protected function execute($value, Path $path)
            {
            }
        };

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($expected);
        (new AssertStdClass())
            ->withProperty($propName, $child, $baseMsg)
            ->parse(
                (object) []
            );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testDefaultingOfMissingProperty(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute(1, Argument::type(Property::class))->shouldBeCalledOnce();
        /** @var Parser $child */
        $child = $parser->reveal();
        (new AssertStdClass())
            ->withDefaultedProperty('prop', 1, $child)
            ->parse(
                (object) []
            );
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testNotDefaultingOfPresentProperty(): void
    {
        $parser = $this->prophesize(Parser::class);
        $parser->execute(1, Argument::type(Property::class))->shouldBeCalledOnce();
        /** @var Parser $child */
        $child = $parser->reveal();
        (new AssertStdClass())
            ->withDefaultedProperty('prop', 'default value is ignored', $child)
            ->parse(
                (object) [
                    'prop' => 1,
                ]
            );
    }


}