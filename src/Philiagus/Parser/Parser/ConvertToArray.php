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
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\ChainableParser;
use Philiagus\Parser\Exception\ParserConfigurationException;

class ConvertToArray implements ChainableParser
{
    use Chainable;

    public const CONVERSION_ARRAY_CAST = 1;
    public const CONVERSION_ARRAY_WITH_KEY = 2;

    /**
     * @var string|int|null
     */
    private $targetedArrayKey = null;

    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function usingCast(): self
    {
        return new self();
    }

    /**
     * @param $key
     *
     * @return static
     * @throws ParserConfigurationException
     */
    public static function creatingArrayWithKey($key): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Array key can only be string or integer');
        }
        $instance = new self();
        $instance->targetedArrayKey = $key;

        return $instance;
    }

    public function parse($value, ?Path $path = null)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($this->targetedArrayKey !== null) {
            return [$this->targetedArrayKey => $value];
        }

        return (array) $value;
    }
}
