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


namespace Philiagus\Parser\Contract;

use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\Logic\Chain;

interface Chainable
{

    /**
     * Chains the Assign-parser to the current parser, assigning the successful
     * result of the current parser to the targeted variable. Any value that is already
     * in the $target variable will be ignored and overwritten if the parser
     * chain reaches the assignment. If errors occur in the chain to the $target the
     * value in $target will not be overwritten.
     *
     * @param mixed $target
     *
     * @return Chain
     * @see Assign
     */
    public function thenAssignTo(mixed &$target): Chain;

    /**
     * Chains another parser to use the result of the current parser. The next parser in the chain
     * is only executed if the current parser resulted in success. If the current parser results in error
     * the chain is broken and the error returned.
     *
     * @param Parser $parser
     *
     * @return Chain
     */
    public function then(Parser $parser): Chain;

    /**
     * Chains the Append-parser to the current parser, appending the successful
     * result of the previous parser to the targeted variable
     *
     * @param array|\ArrayAccess|null $target
     *
     * @return Chain
     * @see Append
     */
    public function thenAppendTo(null|\ArrayAccess|array &$target): Chain;
}
