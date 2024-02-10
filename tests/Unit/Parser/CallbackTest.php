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

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Test\ParserTestBase;

/**
 * @covers \Philiagus\Parser\Parser\Callback
 */
class CallbackTest extends ParserTestBase
{

    public function testNew(): void
    {
        $builder = $this->builder();
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->success(fn() => 'ignored'),
                $builder
                    ->fixedArgument('', 'description')
            )
            ->successProvider(DataProvider::TYPE_ALL, fn($_, $result) => $result === 'ignored');
        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->success(fn(string $a) => strrev($a))
            )
            ->successProvider(DataProvider::TYPE_STRING, fn($start, $result) => strrev($start) === $result);

        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->error(fn() => throw new \Exception('BOOM')),
                $builder
                    ->fixedArgument('', 'description')
            )
            ->values([1, 2, 3, 4])
            ->expectError(fn() => 'BOOM');

        $builder
            ->testStaticConstructor()
            ->arguments(
                $builder
                    ->fixedArgument()
                    ->error(fn(string $a) => null)
            )
            ->provider(~DataProvider::TYPE_STRING)
            ->expectErrorRegex(fn() => '~must be of type string,.*on line 58~');

        $builder->run();
    }

}
