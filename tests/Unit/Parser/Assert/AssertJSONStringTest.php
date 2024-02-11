<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Parser\Assert;

use JsonException;
use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Assert\AssertJSONString;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\Assert\AssertJSONString
 */
class AssertJSONStringTest extends ParserTestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->filter(function($value): bool {
                try {
                    json_encode($value, flags: JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    return false;
                }

                return true;
            })
            ->map(fn($value) => [$json = json_encode($value), fn() => AssertJSONString::new(), $json])
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->filter(fn($v) => !is_string($v) || !json_validate($v))
            ->map(fn($value) => [$value, fn() => AssertJSONString::new()])
            ->provide(false);
    }

    public static function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertJSONString::new()])
            ->provide(false);
    }

    public function test_setConversionExceptionMessage(): void
    {
        $value = 'nope';
        $message = 'MESSAGE {subject.raw} | {message} | {code}';
        $parser = AssertJSONString::new()
            ->setInvalidJSONErrorMessage($message);
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('MESSAGE nope | Syntax error | 4');
        $parser->parse(Subject::default($value));
    }
}
