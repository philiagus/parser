<?php
declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit\Base;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Path\Index;
use Philiagus\Parser\Path\Key;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Path\Property;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testClass()
    {
        $parent = new class('parent') extends Path {

            protected function getDelimiter(): string
            {
                return '';
            }
        };

        $path = new class('path', $parent) extends Path {

            protected function getDelimiter(): string
            {
                return '||';
            }
        };
        self::assertSame([$parent, $path], $path->flat());
        self::assertSame('path', $path->getName());
        self::assertSame($parent, $path->getParent());
        self::assertSame('parent||path', $path->toString());
        self::assertSame('parent||path', (string)$path);
    }

    public function testIndexChain()
    {
        $path = new class('parent') extends Path {
            protected function getDelimiter(): string
            {
                return ':';
            }
        };

        $child = $path->index('index');
        self::assertInstanceOf(Index::class, $child);
        self::assertSame($path, $child->getParent());
        self::assertSame('index', $child->getName());
    }

    public function testPropertyChain()
    {
        $path = new class('parent') extends Path {
            protected function getDelimiter(): string
            {
                return ':';
            }
        };

        $child = $path->property('property');
        self::assertInstanceOf(Property::class, $child);
        self::assertSame($path, $child->getParent());
        self::assertSame('property', $child->getName());
    }

    public function testMetaChain()
    {
        $path = new class('parent') extends Path {
            protected function getDelimiter(): string
            {
                return ':';
            }
        };

        $child = $path->meta('meta');
        self::assertInstanceOf(MetaInformation::class, $child);
        self::assertSame($path, $child->getParent());
        self::assertSame('meta', $child->getName());
    }

    public function testKeyChain()
    {
        $path = new class('parent') extends Path {
            protected function getDelimiter(): string
            {
                return ':';
            }
        };

        $child = $path->key('key');
        self::assertInstanceOf(Key::class, $child);
        self::assertSame($path, $child->getParent());
        self::assertSame('key', $child->getName());
    }
}