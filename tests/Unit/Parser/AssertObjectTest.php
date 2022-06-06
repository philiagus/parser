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
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertObject;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\ValidValueParserTest;
use PHPUnit\Framework\TestCase;

class AssertObjectTest extends TestCase
{
    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;


    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_OBJECT))
            ->map(fn($value) => [$value, fn() => AssertObject::new()])
            ->provide(false);
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_OBJECT))
            ->map(fn($value) => [$value, fn() => AssertObject::new(), $value])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_OBJECT))
            ->map(fn($value) => [$value, fn() => AssertObject::new()])
            ->provide(false);
    }

    public function testInstanceOf(): void
    {
        $parser = AssertObject::instanceOf(Parser::class);
        $parser->parse($parser);
        $parser->assertInstanceOf(get_class($parser));
        $parser->parse($parser);
        self::expectException(ParsingException::class);
        $parser->parse(new \stdClass());
    }
}
