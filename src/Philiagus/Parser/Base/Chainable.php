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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\Logic\Chain;

trait Chainable
{

    /**
     * After the parser is done the resulting value is then appended to the provided target.
     * If the provided target is not an array or not null a ParserException is thrown
     *
     * @param $target
     *
     * @return Chain
     * @throws ParserConfigurationException
     * @see Append::to()
     */
    public function thenAppendTo(&$target): Chain
    {
        if (!$this instanceof ParserContract) {
            throw new \LogicException('Chainable can only be used by implementations of the Parser interface');
        }

        return Chain::parsers($this, Append::to($target));
    }

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

    /**
     * After the parser is done the resulting value is then assigned to the provided target.
     *
     * @param $target
     *
     * @return Chain
     */
    public function thenAssignTo(&$target): Chain
    {
        if (!$this instanceof ParserContract) {
            throw new \LogicException('Chainable can only be used by implementations of the Parser interface');
        }

        return Chain::parsers($this, Assign::to($target));
    }


}
