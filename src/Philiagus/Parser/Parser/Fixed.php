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
use Philiagus\Parser\Base\OverridableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;

/**
 * Class Fixed
 *
 * The Fixed parser ignores its received value and replaces it with a predefined value
 *
 * @package Philiagus\Parser\Parser
 */
class Fixed implements Parser
{
    use Chainable, OverridableChainDescription;

    /** @var mixed */
    private $value;

    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param $value
     *
     * @return static
     */
    public static function value($value): self
    {
        return new self($value);
    }

    public function parse($value, ?Path $path = null)
    {
        return $this->value;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('replace value with fixed value');
    }
}
