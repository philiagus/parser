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

interface Parser
{
    /**
     * Takes the provided value and parses it according to the implementation of this rule
     * The result is both returned and written into the $target provided as a __construct parameter
     *
     * @param mixed $value
     * @param string $path
     *
     * @return mixed
     */
    public function parse($value, string $path = '');
}