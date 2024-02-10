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
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\TestBase;

/**
 * @covers \Philiagus\Parser\Parser\IgnoreInput
 */
class IgnoreInput extends TestBase
{
    use ChainableParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(fn($value) => [$value, fn($value) => \Philiagus\Parser\Parser\IgnoreInput::resultIn(!$value), !$value])
            ->provide(false);
    }

    public static function provideAnyValue(): array
    {
        return (new DataProvider())->provide();
    }

    /**
     * @param mixed $anything
     *
     * @return void
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideAnyValue
     */
    public function testFull($anything): void
    {
        $obj = new \stdClass();
        self::assertSame(
            $obj,
            \Philiagus\Parser\Parser\IgnoreInput::resultIn($obj)->parse(Subject::default($anything))->getValue()
        );
    }
}
