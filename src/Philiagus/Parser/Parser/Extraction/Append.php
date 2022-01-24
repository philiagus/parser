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
use Philiagus\Parser\Base\OverridableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Util\Debug;

/**
 * Whenever this parser is called the value received by this parser is appended to the provided target
 * If the provided target is not an array at that point, Append will convert `null` to an empty array and
 * in all other cases throw a ParserConfigurationException
 */
class Append implements Parser
{
    use Chainable, OverridableChainDescription;

    private array $target;

    /**
     * Append constructor.
     *
     * @param mixed $target
     *
     * @throws ParserConfigurationException
     */
    private function __construct(&$target)
    {
        if (!is_array($target)) {
            if ($target !== null) {
                throw new ParserConfigurationException(
                    'Append parser has received an invalid target of ' . Debug::getType($target)
                );
            }
            $target = [];
        }

        $this->target =& $target;
    }

    /**
     * @param $target
     *
     * @return static
     * @throws ParserConfigurationException
     */
    public static function to(&$target): self
    {
        return new self($target);
    }

    /**
     * @param mixed $value
     * @param Path|null $path
     *
     * @return mixed
     */
    public function parse($value, Path $path = null)
    {
        $this->target[] = $value;

        return $value;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path;
    }
}
