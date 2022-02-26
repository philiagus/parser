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


namespace Philiagus\Parser\Parser\Extraction;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;

/**
 * Stores the value into the provided variable
 */
class Assign implements Parser
{
    use Chainable, OverwritableChainDescription;

    /** @var mixed */
    private $target;

    private function __construct(&$target)
    {
        $this->target = &$target;
    }

    /**
     * Returns a parser that assigns the provided value to the target
     *
     * @param $target
     *
     * @return static
     */
    public static function to(&$target): self
    {
        return new self($target);
    }

    /**
     *
     * @inheritDoc
     */
    public function parse($value, Path $path = null)
    {
        $this->target = $value;

        return $value;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('extract: assign', false);
    }
}
