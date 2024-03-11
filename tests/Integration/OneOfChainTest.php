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
use Philiagus\Parser\Parser\Assert\AssertArray;
use Philiagus\Parser\Parser\Assert\AssertInteger;
use Philiagus\Parser\Parser\Assert\AssertSame;
use Philiagus\Parser\Parser\Logic\IgnoreInput;
use Philiagus\Parser\Parser\Logic\OneOf;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class OneOfChainTest extends TestCase
{
    public function test()
    {
        $result = OneOf::new()
            ->parser(
                AssertArray::new()
                    ->then(IgnoreInput::resultIn('is array'))
            )
            ->parser(
                AssertInteger::new()
                    ->then(IgnoreInput::resultIn('is integer'))
            )
            ->then(
                AssertSame::value('is array')
            )
            ->parse(Subject::default(['array' => 'something']));

        self::assertSame('is array', $result->getValue());
    }
}
