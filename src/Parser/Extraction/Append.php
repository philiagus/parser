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

use ArrayAccess;
use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\RuntimeParserConfigurationException;
use Philiagus\Parser\Util\Debug;

/**
 * Whenever this parser is called the value received by this parser is appended to the provided target
 * If the provided target is not an array at that point, Append will convert `null` to an empty array and
 * in all other cases throw a ParserConfigurationException
 */
class Append implements Parser
{
    use Chainable, OverwritableChainDescription;

    /** @var array|ArrayAccess */
    private $target;

    /**
     * Append constructor.
     *
     * @param mixed $target
     *
     * @throws ParserConfigurationException
     */
    private function __construct(&$target)
    {
        if (!is_array($target) && !$target instanceof ArrayAccess) {
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
     * @param null|array|ArrayAccess $target
     *
     * @return static
     * @throws ParserConfigurationException
     */
    public static function to(&$target): self
    {
        return new self($target);
    }

    /**
     * @inheritDoc
     */
    public function parse($value, Path $path = null)
    {
        if (!is_array($this->target) && !$this->target instanceof ArrayAccess) {
            throw new RuntimeParserConfigurationException(
                'The target of the append parser was altered from an array or ArrayAccess to ' . Debug::getType($this->target)
            );
        }

        $this->target[] = $value;

        return $value;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('extract: appended', false);
    }
}
