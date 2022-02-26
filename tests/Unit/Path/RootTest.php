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
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Path\Root;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Path\Root
 */
class RootTest extends TestCase
{

    public function testItExtendsPath()
    {
        self::assertTrue((new Root('key')) instanceof Path);
    }

    public function testStringConcat()
    {
        $parent = new Root('parent');
        $path = new MetaInformation('Child', $parent);
        self::assertSame([$parent, $path], $path->flat());
        self::assertSame('Child', $path->getDescription());
        self::assertSame($parent, $path->getParent());
        self::assertSame('parent Child', $path->toString());
        self::assertSame('parent Child', (string) $path);
    }

}
