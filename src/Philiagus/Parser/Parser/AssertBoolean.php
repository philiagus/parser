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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Type;

/**
 * Class BooleanPrimitive
 *
 * @package Philiagus\Parser
 */
class AssertBoolean
    extends Parser
    implements Type\AcceptsBoolean
{

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (is_bool($value)) return $value;

        throw new ParsingException($value, 'Provided value is not a boolean', $path);
    }
}