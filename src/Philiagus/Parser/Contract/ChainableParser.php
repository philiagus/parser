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

use Philiagus\Parser\Exception\ParserConfigurationException;

interface ChainableParser extends Parser
{

    /**
     * After the parser has done its job, the resulting value will be provided to this next parser
     * This is especially useful, when using a parser to convert a value from one form to another
     *
     * @param Parser $parser
     *
     * @return self
     */
    public function then(Parser $parser): self;

    /**
     * After the parser has done its job, the resulting value will be assigned to the provided target
     *
     * @param $target
     *
     * @return self
     */
    public function thenAssignTo(&$target): self;

    /**
     * After the parser has done its job, the resulting value will be appended to the target
     * If the provided target is NULL it will be converted to an empty array.
     * If the provided target is not an array and not NULL a ParserConfigurationException is thrown.
     *
     * @param $target
     *
     * @return self
     * @throws ParserConfigurationException
     */
    public function thenAppendTo(&$target): self;

}
