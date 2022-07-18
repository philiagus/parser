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

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertStdClass;
use Philiagus\Parser\Parser\ParseStdClass;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class ParseStdClassTest extends TestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        $value = new \stdClass();

        return [
            [$value, fn() => ParseStdClass::new(), $value],
        ];
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => !$value instanceof \stdClass)
            ->map(fn($value) => [$value, fn() => ParseStdClass::new()])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => !$value instanceof \stdClass)
            ->map(fn($value) => [$value, fn() => ParseStdClass::new()])
            ->provide(false);
    }

    public function testFullModification(): void
    {
        $testPropertiesAndValues = function (ParseStdClass $parser, $expected): void {
            $expected = (array) $expected;
            $keys = array_map('strval', array_keys($expected));
            $values = array_values($expected);
            $parser->givePropertyNames($this->prophesizeParser([[$keys, $keys]]));
            $parser->givePropertyValues($this->prophesizeParser([[$values, $values]]));
        };

        $baseValue = (object) ['a' => 1, 'c' => 3];
        $parser = ParseStdClass::new();
        $testPropertiesAndValues($parser, $baseValue);
        $parser->defaultWith((object) ['a' => 'ignored', 'b' => 2]);
        $testPropertiesAndValues($parser, ['a' => 1, 'c' => 3, 'b' => 2]);
        $parser->modifyEachPropertyName($this->prophesizeParser([
            ['a', 'first'],
            ['b', 'second'],
            ['c', 'third'],
        ]));
        $testPropertiesAndValues($parser, ['first' => 1, 'third' => 3, 'second' => 2]);
        $parser->modifyPropertyValue('third', $this->prophesizeParser([
            [3, 13],
        ]));
        $testPropertiesAndValues($parser, ['first' => 1, 'third' => 13, 'second' => 2]);
        $parser->modifyOptionalPropertyValue('not exists', $this->prophesizeUncalledParser());
        $testPropertiesAndValues($parser, ['first' => 1, 'third' => 13, 'second' => 2]);
        $parser->modifyOptionalPropertyValue('second', $this->prophesizeParser([
            [2, 12],
        ]));
        $testPropertiesAndValues($parser, ['first' => 1, 'third' => 13, 'second' => 12]);
        $parser->modifyEachPropertyValue($this->prophesizeParser([
            [1, 10],
            [12, 20],
            [13, 30],
        ]));
        $testPropertiesAndValues($parser, ['first' => 10, 'third' => 30, 'second' => 20]);
        $parser->modifyEachPropertyName(
            $this->prophesizeParser([
                ['first', 'reduce'],
                ['second', 'reduce'],
                ['third', 'reduce'],
            ])
        );
        $testPropertiesAndValues($parser, ['reduce' => 20]);
        $parser->defaultProperty('defaulted', 90);
        $testPropertiesAndValues($parser, ['reduce' => 20, 'defaulted' => 90]);
        $parser->parse(Subject::default((object) ['a' => 1, 'c' => 3]));
    }

    public function provideInvalidPropertyNames(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->provide();
    }

    /**
     * @dataProvider provideInvalidPropertyNames
     */
    public function testExceptionOnInvalidPropertyNameModify($invalidName): void
    {
        $parser = ParseStdClass::new()
            ->modifyEachPropertyName(
                $this->prophesizeParser([
                    ['name', $invalidName],
                ])
            );

        self::expectException(ParserConfigurationException::class);
        $parser->parse(Subject::default((object) ['name' => 'value']));
    }

    public function test_modifyOptionalProperty_cloning(): void
    {
        $source = (object) ['name' => 'value'];
        $result = ParseStdClass::new()
            ->modifyOptionalPropertyValue('name', $this->prophesizeParser([['value', 'new value']]))
            ->parse(Subject::default($source));
        self::assertNotSame($source, $result);
    }

    public function test_modifyPropertyValue_cloning(): void
    {
        $source = (object) ['name' => 'value'];
        $result = ParseStdClass::new()
            ->modifyPropertyValue('name', $this->prophesizeParser([['value', 'new value']]))
            ->parse(Subject::default($source));
        self::assertNotSame($source, $result);
    }

    public function test_modifyPropertyValue_missingProperty(): void
    {
        self::expectException(ParsingException::class);
        ParseStdClass::new()
            ->modifyPropertyValue('name', $this->prophesizeUncalledParser())
            ->parse(Subject::default((object) []));
    }

    public function test_defaultProperty_cloning(): void
    {
        $source = (object) [];
        $result = ParseStdClass::new()
            ->defaultProperty('name', 'value')
            ->parse(Subject::default($source));
        self::assertNotSame($source, $result);
        self::assertEquals((object)['name' => 'value'], $result->getValue());
    }

    public function test_defaultProperty_notReplacing(): void
    {
        $source = (object) ['name' => 'value'];
        $result = ParseStdClass::new()
            ->defaultProperty('name', 'nope')
            ->parse(Subject::default($source));
        self::assertSame($source, $result->getValue());
        self::assertEquals((object)['name' => 'value'], $result->getValue());
    }
}
