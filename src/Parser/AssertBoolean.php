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
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;

/**
 * Class BooleanPrimitive
 *
 * @package Philiagus\Parser
 */
class AssertBoolean implements Parser
{
    use Chainable, OverwritableChainDescription, TypeExceptionMessage;


    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function new(): self
    {
        return new self();
    }

    public function parse($value, Path $path = null)
    {
        if (is_bool($value)) return $value;

        $this->throwTypeException($value, $path);
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not a boolean';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert boolean', false);
    }
}
