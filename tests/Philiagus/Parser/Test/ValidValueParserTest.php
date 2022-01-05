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

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Util\Debug;

trait ValidValueParserTest
{

    abstract public function provideValidValuesAndParsersAndResults(): array;
    abstract public static function assertTrue($condition, string $message = ''): void;

    /**
     * @dataProvider provideValidValuesAndParsersAndResults
     */
    public function testThatItAcceptsValidValues($value, Parser $parser, $expected): void
    {
        $result = $parser->parse($value);
        self::assertTrue(
            DataProvider::isSame($expected, $result),
            Debug::stringify($expected) . ' is not equal to ' . Debug::stringify($result)
        );
    }

}
