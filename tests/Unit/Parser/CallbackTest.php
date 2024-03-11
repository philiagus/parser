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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Callback;
use Philiagus\Parser\Test\ParserTestBase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Callback::class)]
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
            ->expectErrorRegex(fn() => '~must be of type string,.*on line \d+~');

        $builder->run();
    }

    public function test_setErrorMessage(): void
    {
        $throwable = new \LogicException('EXCEPTION_MESSAGE', 123);
        $parser = Callback::new(fn() => throw $throwable)
            ->setErrorMessage('| {value} | {throwable.raw} | {throwableMessage} | {throwableCode} | {throwableFile} | {throwableLine} |');

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage("| SUBJECT | $throwable | {$throwable->getMessage()} | {$throwable->getCode()} | {$throwable->getFile()} | {$throwable->getLine()} |");
        $parser->parse(Subject::default('SUBJECT'));
    }

}
