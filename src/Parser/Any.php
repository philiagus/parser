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

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;

class Any implements Parser
{
    use Chainable, OverwritableChainDescription;

    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function new(): self
    {
        return new self();
    }

    public function parse($value, Path $path = null)
    {
        return $value;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path;
    }
}
