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

namespace Philiagus\Parser\Test\Integration;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\AssertArray;
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Parser\Parser\AssertSame;
use Philiagus\Parser\Parser\Fixed;
use Philiagus\Parser\Parser\Logic\OneOf;
use PHPUnit\Framework\TestCase;

class OneOfChainTest extends TestCase
{
    public function test()
    {
        $result = OneOf::new()
            ->parser(
                AssertArray::new()
                    ->then(
                        Fixed::value('is array')
                    )
            )
            ->parser(
                AssertInteger::new()
                    ->then(Fixed::value('is integer'))
            )
            ->then(
                AssertSame::value('is array')
            )
            ->parse(Subject::default(['array' => 'something']));

        self::assertSame('is array', $result->getValue());
    }
}
