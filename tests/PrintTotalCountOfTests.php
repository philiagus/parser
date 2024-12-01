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

namespace Philiagus\Parser\Test;

use Philiagus\Parser\Test\ParserTestBase\CaseBuilder;
use Philiagus\Parser\Test\ParserTestBase\TestCase;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class PrintTotalCountOfTests implements ExecutionFinishedSubscriber, Extension
{

    #[\Override] public function notify(ExecutionFinished $event): void
    {
        print "Total number of test builders created:  " . number_format(CaseBuilder::$totalNumberCreated) . PHP_EOL;
        print "Total generated sub-testcases executed: " . number_format(TestCase::$totalRun) . PHP_EOL;
    }

    #[\Override] public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber($this);
    }
}
