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

namespace Philiagus\Parser\Test\Unit\Base;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Parser\Any;
use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\Logic\Chain;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Base\Chainable
 */
class ChainableTest extends TestCase
{
    public function testThen_LogicException(): void
    {
        $instance = new class() {
            use Chainable;
        };

        self::expectException(\LogicException::class);
        $instance->then(Any::new());
    }

    public function testThenAssignTo_LogicException(): void
    {
        $instance = new class() {
            use Chainable;
        };

        self::expectException(\LogicException::class);
        $instance->thenAssignTo($target);
    }

    public function testThenAppendTo_LogicException(): void
    {
        $instance = new class() {
            use Chainable;
        };

        self::expectException(\LogicException::class);
        $instance->thenAppendTo($target);
    }

    public function testThen(): void
    {
        self::assertEquals(
            $this->getInstance()->then($this->getInstance()),
            Chain::parsers($this->getInstance(), $this->getInstance())
        );
    }

    private function getInstance(): Contract\Parser&Contract\Chainable
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return new class() implements Contract\Parser, Contract\Chainable {
            use Chainable;

            public function parse(Contract\Subject $subject): Contract\Result
            {
                return new Result($subject, null, []);
            }
        };
    }

    public function testThenAssignTo(): void
    {
        $target = new \stdClass();
        self::assertEquals(
            $this->getInstance()->thenAssignTo($target),
            Chain::parsers($this->getInstance(), Assign::to($target))
        );
    }

    public function testThenAppendTo(): void
    {
        $target = new \SplDoublyLinkedList();
        self::assertEquals(
            $this->getInstance()->thenAppendTo($target),
            Chain::parsers($this->getInstance(), Append::to($target))
        );
    }
}
