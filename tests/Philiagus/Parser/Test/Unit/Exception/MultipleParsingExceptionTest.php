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

namespace Philiagus\Parser\Test\Unit\Exception;

use Philiagus\Parser\Exception\MultipleParsingException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Root;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Exception\MultipleParsingException
 */
class MultipleParsingExceptionTest extends TestCase
{

    public function testConstruct(): void
    {
        $exceptions = [
            new ParsingException('value', '', new Root('')),
            new ParsingException('value', '', new Root('')),
            new ParsingException('value', '', new Root('')),
        ];

        $path = new Root('root');
        $instance = new MultipleParsingException('value', 'message', $path, $exceptions);
        self::assertSame('message', $instance->getMessage());
        self::assertSame($path, $instance->getPath());
        self::assertSame($exceptions, $instance->getParsingExceptions());
    }

    public function testConstructException(): void
    {
        $this->expectException(\LogicException::class);
        new MultipleParsingException('value', 'message', new Root(''), [false]);
    }

}
