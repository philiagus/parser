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

namespace Philiagus\Test\Parser\Mock;

use Philiagus\Parser\Base\Path;

class Parser extends \Philiagus\Parser\Base\Parser
{

    protected function execute($value, Path $path)
    {
        return $value;
    }
}