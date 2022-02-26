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

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Parser\Logic\Chain;

trait Chainable
{

    /**
     * Chains another parser to use the result of the current parser
     *
     * @param ParserContract $parser
     *
     * @return Chain
     */
    public function then(ParserContract $parser): Chain
    {
        if (!$this instanceof ParserContract) {
            throw new \LogicException('Chainable can only be used by implementations of the Parser interface');
        }

        return Chain::parsers($this, $parser);
    }

}
