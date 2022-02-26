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

namespace Philiagus\Parser\Test;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class TestBase extends TestCase
{
    protected function parserProphecy(
        $expectedValue,
        $result = null,
        $expectedPath = null
    ): Parser
    {
        $parser = $this->prophesize(Parser::class);
        $parser
            ->parse(
                $expectedValue,
                $expectedPath ?? Argument::that(fn($arg) => $arg === null || $arg instanceof Path)
            )
            ->shouldBeCalled()
            ->willReturn($result ?? $expectedValue);

        return $parser->reveal();
    }

}
