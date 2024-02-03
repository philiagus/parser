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
use Philiagus\Parser\Parser\ParseJSONString;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\ParseJSONString
 */
class ParseJSONStringTest extends TestBase
{

    use ChainableParserTestTrait, InvalidValueParserTestTrait, ValidValueParserTestTrait;

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->addCase('not json string', '?=)(/&%')
            ->map(fn($value) => [$value, fn() => ParseJSONString::new()])
            ->provide(false);
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->filter(function ($value) {
                @json_encode($value);

                return json_last_error() === JSON_ERROR_NONE;
            })
            ->map(fn($value) => [json_encode($value), fn() => ParseJSONString::new(), $value])
            ->provide(false);
    }

    public function test_setConversionExceptionMessage(): void
    {
        $value = 'nope';
        $message = 'MESSAGE {subject.raw}';
        $parser = ParseJSONString::new()
            ->setConversionExceptionMessage($message);
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('MESSAGE nope');
        $parser->parse(Subject::default($value));
    }

    public function test_setObjectsAsArrays(): void
    {
        $value = '{"a":1}';
        self::assertEquals((object) ['a' => 1], ParseJSONString::new()->parse(Subject::default($value))->getValue());
        self::assertSame(['a' => 1], ParseJSONString::new()->setObjectsAsArrays()->parse(Subject::default($value))->getValue());
        self::assertSame(['a' => 1], ParseJSONString::new()->setObjectsAsArrays(true)->parse(Subject::default($value))->getValue());
        self::assertEquals((object) ['a' => 1], ParseJSONString::new()->setObjectsAsArrays(false)->parse(Subject::default($value))->getValue());
    }

    public function test_setMaxDepth(): void
    {
        $value = '[[[[[[5]]]]]]';
        self::assertSame(
            json_decode($value),
            ParseJSONString::new()
                ->setMaxDepth(7)
                ->parse(Subject::default($value))
                ->getValue()
        );
        self::expectException(ParsingException::class);
        ParseJSONString::new()->setMaxDepth(1)->parse(Subject::default($value));
    }

    public function test_setMaxDepth_configurationException(): void
    {
        self::expectException(ParserConfigurationException::class);
        ParseJSONString::new()
            ->setMaxDepth(0);
    }

    public function test_setBigintAsString(): void
    {
        $value = PHP_INT_MAX . '1';
        self::assertSame(
            (float) $value,
            ParseJSONString::new()
                ->parse(Subject::default($value))
                ->getValue()
        );

        self::assertSame(
            $value,
            ParseJSONString::new()
                ->setBigintAsString()
                ->parse(Subject::default($value))
                ->getValue()
        );
    }

}
