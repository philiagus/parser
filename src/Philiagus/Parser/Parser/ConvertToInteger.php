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
use Philiagus\Parser\Exception;
use Philiagus\Parser\Util\Debug;

/**
 * Takes any input and attempts a loss free conversion of the provided value into a valid integer value
 */
class ConvertToInteger implements ChainableParser
{
    use Chainable;

    private string $typeExceptionMessage = 'Variable of type {value.type} could not be converted to an integer';

    private function __construct()
    {

    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * Sets the message of the exception thrown when the provided value could not be converted to an integer
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function setTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    public function parse($value, ?Path $path = null)
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value) && !is_nan($value) && !is_infinite($value)) {
            // float conversion
            if ((string) $value == (string) (int) $value) {
                return (int) $value;
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

        throw new Exception\ParsingException(
            $value,
            Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
            $path
        );
    }
}
