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

namespace Philiagus\Parser\Type;

use Philiagus\Parser\Base\Path;

/**
 * This is a dummy interface used for type hinting.
 * Every parser that is capable of accepting an object value must implement this interface
 *
 * @package Philiagus\Parser\Type
 */
interface AcceptsObject extends AcceptsMixed
{
    public function parse($value, Path $path = null);
}