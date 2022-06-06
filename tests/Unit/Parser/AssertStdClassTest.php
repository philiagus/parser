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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertStdClass;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class AssertStdClassTest extends TestBase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        $value = new \stdClass();

        return [
            [$value, fn() => AssertStdClass::new(), $value],
        ];
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => !$value instanceof \stdClass)
            ->map(fn($value) => [$value, fn() => AssertStdClass::new()])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider())
            ->filter(fn($value) => !$value instanceof \stdClass)
            ->map(fn($value) => [$value, fn() => AssertStdClass::new()])
            ->provide(false);
    }

    public function test_givePropertyValue(): void
    {
        $path = new Root();
        $parser = AssertStdClass::new()
            ->givePropertyValue('name', $this->prophesizeParser(['value'], $path->propertyValue('name')));
        $parser->parse((object)[
            'name' => 'value'
        ], $path);
        self::expectException(ParsingException::class);
        $parser->parse((object)['another' => 'another'], $path);
    }

    public function test_giveOptionalPropertyValue(): void
    {
        $path = new Root();
        $parser = AssertStdClass::new()
            ->giveOptionalPropertyValue('name', $this->prophesizeParser(['value'], $path->propertyValue('name')))
            ->giveOptionalPropertyValue('nope', $this->prophesizeUncalledParser());
        $object = (object)[
            'name' => 'value'
        ];
        self::assertSame($object, $parser->parse($object, $path));
    }

    public function test_giveDefaultedPropertyValue(): void
    {
        $path = new Root();
        $parser = AssertStdClass::new()
            ->giveDefaultedPropertyValue('name', 'default', $this->prophesizeParser(['value'], $path->propertyValue('name')))
            ->giveDefaultedPropertyValue('defaulted', 'default2', $this->prophesizeParser(['default2'], $path->propertyValue('defaulted')))
            ;
        $object = (object)[
            'name' => 'value'
        ];
        self::assertSame($object, $parser->parse($object, $path));
    }

    public function test_givePropertyNames(): void
    {
        $object = [
            'a' => 1,
            'b' => [1,2,3],
            'cc' => null
        ];
        AssertStdClass::new()
            ->givePropertyNames($this->prophesizeParser([[array_keys($object)]]))
            ->parse((object)$object);
    }

    public function test_giveEachPropertyName(): void
    {
        $object = [
            'a' => 1,
            'b' => [1,2,3],
            'cc' => null
        ];
        AssertStdClass::new()
            ->giveEachPropertyName($this->prophesizeParser(array_keys($object)))
            ->parse((object)$object);
    }

    public function test_giveEachPropertyValue(): void
    {
        $object = [
            'a' => 1,
            'b' => [1,2,3],
            'cc' => null
        ];
        AssertStdClass::new()
            ->giveEachPropertyValue($this->prophesizeParser(array_map(fn($v) => [$v], array_values($object))))
            ->parse((object)$object);
    }

    public function test_givePropertyCount(): void
    {
        AssertStdClass::new()
            ->givePropertyCount($this->prophesizeParser([2]))
            ->parse((object)['a', 'b']);
    }


}
