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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Util\Debug;

/**
 * Takes any input and attempts a loss free conversion of the provided value into a valid integer value
 */
class ConvertToInteger
    extends Parser
{

    private $typeExceptionMessage = 'Variable of type {value.type} could not be converted to an integer';

    /**
     * Overwrites the message of the exception thrown when the provided value could not be converted to an integer
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
    public function overwriteTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_int($value)) {
            if (is_float($value) && !is_nan($value) && !is_infinite($value)) {
                // float conversion
                if ((string) $value == (string) (int) $value) {
                    $value = (int) $value;
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
                        $value = $compareInteger;
                    }
                }
            }

            if (!is_int($value)) {
                throw new Exception\ParsingException(
                    $value,
                    Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                    $path
                );
            }
        }


        return $value;
    }
}