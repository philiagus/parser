<?php
declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Path\Index;
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