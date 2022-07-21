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

namespace Philiagus\Parser\Test\ParserTestBase;

use Generator;

interface Argument {

    public function generate(mixed $subjectValue, array $generatedArgs, array $successes): Generator;

    public function getErrorMeansFail(): bool;

}
