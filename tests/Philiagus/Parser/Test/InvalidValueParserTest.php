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

namespace Philiagus\Parser\Test;

use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;

trait InvalidValueParserTest
{

    abstract public function provideInvalidValuesAndParsers(): array;
    abstract public function expectException(string $exception): void;

    /**
     * @param $value
     * @param Parser $parser
     *
     * @throws ParsingException
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @dataProvider provideInvalidValuesAndParsers
     */
    public function testThatItBlocksInvalidValues($value, Parser $parser): void
    {
        self::expectException(ParsingException::class);
        $parser->parse($value);
    }


}
