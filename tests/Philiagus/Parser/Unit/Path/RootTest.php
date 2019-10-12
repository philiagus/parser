<?php
declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Path\Root;
use PHPUnit\Framework\TestCase;

class RootTest extends TestCase
{

    public function testItExtendsPath()
    {
        self::assertTrue((new Root('key')) instanceof Path);
    }

    public function testStringConcat()
    {
        $parent = new Root('parent');
        $path = new Root('Child', $parent);
        self::assertSame([$parent, $path], $path->flat());
        self::assertSame('Child', $path->getName());
        self::assertSame($parent, $path->getParent());
        self::assertSame('parentChild', $path->toString());
        self::assertSame('parentChild', (string) $path);
    }

}