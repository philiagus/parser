<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Path;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Path\PropertyValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Path\PropertyValue
 */
class PropertyValueTest extends TestCase
{

    public function testItExtendsPath()
    {
        self::assertTrue((new PropertyValue('key')) instanceof Path);
    }

    public function testStringConcat()
    {
        $parent = new PropertyValue('parent');
        $path = new PropertyValue('child', $parent);
        self::assertSame([$parent, $path], $path->flat());
        self::assertSame('child', $path->getDescription());
        self::assertSame($parent, $path->getParent());
        self::assertSame('.parent.child', $path->toString());
        self::assertSame('.parent.child', (string) $path);
    }

}