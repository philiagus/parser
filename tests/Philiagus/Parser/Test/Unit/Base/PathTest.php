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

namespace Philiagus\Parser\Test\Unit\Base;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Path\ArrayElement;
use Philiagus\Parser\Path\ArrayKey;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Path\PropertyValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Base\Path
 */
class PathTest extends TestCase
{
    public function testClass(): void
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
        self::assertSame('parent||path', (string) $path);
    }

    public function testIndexChain(): void
    {
        $path = new class('parent') extends Path {
            protected function getDelimiter(): string
            {
                return ':';
            }
        };

        $child = $path->arrayElement('index');
        self::assertInstanceOf(ArrayElement::class, $child);
        self::assertSame($path, $child->getParent());
        self::assertSame('index', $child->getName());
    }

    public function testPropertyChain(): void
    {
        $path = new class('parent') extends Path {
            protected function getDelimiter(): string
            {
                return ':';
            }
        };

        $child = $path->propertyValue('property');
        self::assertInstanceOf(PropertyValue::class, $child);
        self::assertSame($path, $child->getParent());
        self::assertSame('property', $child->getName());
    }

    public function testMetaChain(): void
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

    public function testKeyChain(): void
    {
        $path = new class('parent') extends Path {
            protected function getDelimiter(): string
            {
                return ':';
            }
        };

        $child = $path->arrayKey('key');
        self::assertInstanceOf(ArrayKey::class, $child);
        self::assertSame($path, $child->getParent());
        self::assertSame('key', $child->getName());
    }
}
