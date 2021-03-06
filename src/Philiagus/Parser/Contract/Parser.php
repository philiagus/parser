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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;

interface Parser
{
    /**
     * @param mixed $value
     * @param Path|null $path
     *
     * @return mixed
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function parse($value, Path $path = null);
}