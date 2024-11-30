<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Base\Subject;

use Philiagus\Parser\Base\Subject\Memory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Memory::class)]
class MemoryTest extends TestCase
{

    public function testFull(): void
    {
        $memory = new Memory();
        self::assertSame(null, $memory->get($memory));
        self::assertSame('yes', $memory->get($memory, 'yes'));
        self::assertSame($memory, $memory->get($memory, $memory));
        self::assertFalse($memory->has($memory));

        $memory->set($memory, $memory);
        self::assertSame($memory, $memory->get($memory));
        self::assertTrue($memory->has($memory));
    }

}
