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

use Philiagus\Parser\Exception\MultipleParsingException;
use Philiagus\Parser\Exception\ParsingException;
use PHPUnit\Framework\TestCase;

class MultipleParsingExceptionTest extends TestCase
{

    public function testConstruct(): void
    {
        $exceptions = [
            new ParsingException('value', '', ''),
            new ParsingException('value', '', ''),
            new ParsingException('value', '', ''),
        ];

        $instance = new MultipleParsingException('value', 'message', 'path', $exceptions);
        self::assertSame('message', $instance->getMessage());
        self::assertSame(['path'], $instance->getPath());
        self::assertSame($exceptions, $instance->getParsingExceptions());
    }

    public function testConstructException(): void
    {
        self::expectException(\LogicException::class);
        new MultipleParsingException('value', 'message', 'path', [false]);
    }

}
