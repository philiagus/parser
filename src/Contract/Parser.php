<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Contract;

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Exception\RuntimeParserConfigurationException;

interface Parser
{
    /**
     * @param mixed $value
     * @param Path|null $path
     *
     * @return mixed
     * @throws RuntimeParserConfigurationException
     * @throws ParsingException
     */
    public function parse($value, ?Path $path = null);

    /**
     * Returns a path representing this parser when chained
     * Most times using the Path::chain method to append a string to the provided path
     *
     * @param Path $path
     *
     * @return null|Path
     * @see Path::chain
     */
    public function getChainedPath(Path $path): ?Path;
}
