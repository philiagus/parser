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
use Philiagus\Parser\Path\Index;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{

    public function testItExtendsPath()
    {
        self::assertTrue((new Index('key')) instanceof Path);
    }

    public function testStringConcat()
    {
        $parentParentParent = new Index('2');
        $parentParent = new Index('1', $parentParentParent);
        $parent = new Index('parent', $parentParent);
        $path = new Index('child', $parent);
        self::assertSame([$parentParentParent, $parentParent, $parent, $path], $path->flat());
        self::assertSame('child', $path->getName());
        self::assertSame($parent, $path->getParent());
        self::assertSame('2 #1 > parent > child', $path->toString());
        self::assertSame('2 #1 > parent > child', (string) $path);
    }

}