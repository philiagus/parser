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

use Philiagus\Parser\Contract;
use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\Logic\Chain;

trait Chainable
{

    /**
     * @see Contract\Chainable::thenAssignTo()
     */
    public function thenAssignTo(&$target): Chain
    {
        return $this->then(Assign::to($target));
    }

    /**
     * @see Contract\Chainable::then()
     */
    public function then(Contract\Parser $parser): Chain
    {
        /** @noinspection PhpInstanceofIsAlwaysTrueInspection */
        if (!$this instanceof Contract\Parser) {
            throw new \LogicException('Chainable can only be used by implementations of the Parser interface');
        }

        return Chain::parsers($this, $parser);
    }

    /**
     * @see Contract\Chainable::thenAppendTo()
     */
    public function thenAppendTo(null|\ArrayAccess|array &$target): Chain
    {
        return $this->then(Append::to($target));
    }

}
