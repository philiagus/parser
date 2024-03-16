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

use Philiagus\Parser\Test\ParserTestBase\CaseBuilder;

abstract class ParserTestBase extends TestBase
{

    public function builder(): CaseBuilder
    {
        return new CaseBuilder($this::getCoveredClass());
    }
}
