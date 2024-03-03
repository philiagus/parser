<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Exception;

use Philiagus\Parser\Exception\RuntimeParserConfigurationException;
use Philiagus\Parser\Test\TestBase;

/**
 * @covers \Philiagus\Parser\Exception\RuntimeParserConfigurationException
 */
class RuntimeParserConfigurationExceptionTest extends TestBase
{

    public function testFull()
    {
        $parent = new \Exception();
        $exception = new RuntimeParserConfigurationException('message', $parent);
        self::assertSame('message', $exception->getMessage());
        self::assertSame($parent, $exception->getPrevious());
    }

}
