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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Fixed;
use Philiagus\Parser\Test\ChainableParserTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Parser\Fixed
 */
class FixedTest extends TestCase
{
    use ChainableParserTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(fn($value) => [$value, fn($value) => Fixed::value(!$value), !$value])
            ->provide(false);
    }

    public function provideAnyValue(): array
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
            Fixed::value($obj)->parse($anything)
        );
    }
}