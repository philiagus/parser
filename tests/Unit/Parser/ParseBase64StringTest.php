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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ParseBase64String;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\ParseBase64String
 */
class ParseBase64StringTest extends TestBase
{
    use ChainableParserTestTrait, InvalidValueParserTestTrait, ValidValueParserTestTrait;

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->addCase('not base 64 string', '?=)(/&%')
            ->map(fn($value) => [$value, fn() => ParseBase64String::new()])
            ->provide(false);
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [base64_encode($value), fn() => ParseBase64String::new(), $value])
            ->provide(false);
    }

    public function test_setNotBase64ExceptionMessage(): void
    {
        $value = '$$$';
        $parser = ParseBase64String::new()
            ->setNotBase64ExceptionMessage('MESSAGE {subject.raw}');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('MESSAGE $$$');
        $parser->parse(Subject::default($value));
    }

    public function test_setStrict(): void
    {
        $string = base64_encode('hallo welt') . 'üüü';
        self::assertSame('hallo welt', ParseBase64String::new()->setStrict(false)->parse(Subject::default($string))->getValue());
        self::expectException(ParsingException::class);
        ParseBase64String::new()->parse(Subject::default($string));
    }
}
