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
use Philiagus\Parser\Path\Property;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{

    public function testItExtendsPath()
    {
        self::assertTrue((new Property('key')) instanceof Path);
    }

    public function testStringConcat()
    {
        $parent = new Property('parent');
        $path = new Property('child', $parent);
        self::assertSame([$parent, $path], $path->flat());
        self::assertSame('child', $path->getName());
        self::assertSame($parent, $path->getParent());
        self::assertSame('parent.child', $path->toString());
        self::assertSame('parent.child', (string) $path);
    }

}