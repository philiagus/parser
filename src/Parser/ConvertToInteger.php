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
 * Takes any input and attempts a loss free conversion of the provided value into a valid integer value
 */
class ConvertToInteger implements Parser
{
    use Chainable, OverwritableChainDescription, TypeExceptionMessage;

    private function __construct()
    {

    }

    public static function new(): self
    {
        return new self();
    }

    public function parse($value, ?Path $path = null)
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            // float conversion
            if (!is_nan($value) && !is_infinite($value)) {
                if ($value == (float) (int) $value) {
                    return (int) $value;
                }
            }
        } elseif (is_string($value)) {
            // string conversion
            if (preg_match('~^(-|)0*([0-9]+)$~', $value, $matches) === 1) {
                if ($matches[2] === '0') {
                    $compareString = '0';
                } else {
                    $compareString = $matches[1] . $matches[2];
                }
                $compareInteger = (int) $compareString;
                if ((string) $compareInteger === $compareString) {
                    return $compareInteger;
                }
            }
        }

        $this->throwTypeException($value, $path);
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Variable of type {value.type} could not be converted to an integer';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('convert to integer', false);
    }
}
