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

class AssertNull extends Parser
{

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if ($value === null) return null;

        throw new ParsingException($value, 'Provided value is not null', $path);
    }
}