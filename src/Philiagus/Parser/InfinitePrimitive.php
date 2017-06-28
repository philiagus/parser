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

namespace Philiagus\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Type\AcceptsFloat;

class InfinitePrimitive extends Parser implements AcceptsFloat
{

    /**
     * @inheritdoc
     */
    protected function convert($value, string $path)
    {
        if (is_float($value) && is_infinite($value)) return INF;

        throw new ParsingException('Provided value is not INF', $path);
    }
}