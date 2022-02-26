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


namespace Philiagus\Parser\Test\Integration\Path;

use Philiagus\Parser\Path\Root;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{

    public function test()
    {
        $path = (new Root('root'))
            ->chain('chain')
            ->chain('chain X', false)
            ->arrayElement('array element')
            ->arrayElement('array element X', false)
            ->arrayKey('array key')
            ->arrayKey('array key X', false)
            ->propertyValue('property value')
            ->propertyValue('property value X', false)
            ->propertyName('property name')
            ->propertyName('property name X', false)
            ->meta('meta')
            ->meta('meta X', false);

        self::assertSame(
            "root" .
            "> chain >> chain X >" .
            "[array element][array element X]" .
            " key 'array key' key 'array key X'" .
            ".property value.property value X" .
            " property name property name property name property name X" .
            " meta meta X",
            (string) $path
        );
        self::assertSame((string) $path, $path->toString(false));

        self::assertSame(
            "root> chain >[array element] key 'array key'.property value property name property name meta",
            $path->toString(true)
        );
    }
}
