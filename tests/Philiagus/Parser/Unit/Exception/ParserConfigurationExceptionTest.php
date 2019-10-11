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

namespace Philiagus\Test\Parser\Unit\Exception;

use Exception;
use Philiagus\Parser\Exception\ParserConfigurationException;
use PHPUnit\Framework\TestCase;

class ParserConfigurationExceptionTest extends TestCase
{

    public function testConstructWithoutPrevious(): void
    {
        $exception = new ParserConfigurationException('message');

        self::assertSame('message', $exception->getMessage());
        self::assertNull($exception->getPrevious());
    }

    public function testConstructWithPrevious(): void
    {
        $previous = new Exception();
        $exception = new ParserConfigurationException('message', $previous);

        self::assertSame('message', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }
}
