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

namespace Philiagus\Test\Parser\Unit;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Path\Key;
use PHPUnit\Framework\TestCase;

class KeyTest extends TestCase
{

    public function testItExtendsPath()
    {
        self::assertTrue((new Key('key')) instanceof Path);
    }

    public function testStringConcat()
    {
        $parent = new Key('parent');
        $path = new Key('child', $parent);
        self::assertSame([$parent, $path], $path->flat());
        self::assertSame('child', $path->getName());
        self::assertSame($parent, $path->getParent());
        self::assertSame('parent key child', $path->toString());
        self::assertSame('parent key child', (string) $path);
    }

}