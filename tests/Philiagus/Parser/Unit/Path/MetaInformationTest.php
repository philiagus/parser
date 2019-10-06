<?php
declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Path\MetaInformation;
use PHPUnit\Framework\TestCase;

class MetaInformationTest extends TestCase
{

    public function testItExtendsPath()
    {
        self::assertTrue((new MetaInformation('key')) instanceof Path);
    }

    public function testStringConcat()
    {
        $parent = new MetaInformation('parent');
        $path = new MetaInformation('child', $parent);
        self::assertSame([$parent, $path], $path->flat());
        self::assertSame('child', $path->getName());
        self::assertSame($parent, $path->getParent());
        self::assertSame('parent child', $path->toString());
        self::assertSame('parent child', (string) $path);
    }

}