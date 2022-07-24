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


namespace Philiagus\Parser\Contract;

use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\Logic\Chain;

interface Chainable
{

    /**
     * Chains the Assign parser to the current parser, assigning the successful
     * result of the previous parser to the targeted variable
     *
     * @param $target
     *
     * @return Chain
     * @see Assign
     */
    public function thenAssignTo(&$target): Chain;

    /**
     * Chains another parser to use the result of the current parser
     *
     * @param Parser $parser
     *
     * @return Chain
     */
    public function then(Parser $parser): Chain;

    /**
     * Chains the Append parser to the current parser, appending the successful
     * result of the previous parser to the targeted variable
     *
     * @param $target
     *
     * @return Chain
     * @see Append
     */
    public function thenAppendTo(&$target): Chain;
}
