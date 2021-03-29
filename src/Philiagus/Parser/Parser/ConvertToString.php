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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class ConvertToString extends Parser
{

    private $typeExceptionMessage = 'Variable of type {value.type} could not be converted to a string';

    /**
     * @var array|null
     */
    private $booleanValues = null;

    /**
     * @var null|string[]
     */
    private $implode = null;

    /**
     * Sets the exception message when the value could not be converted to a string
     *
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

    /**
     * @param string $true
     * @param string $false
     *
     * @return $this
     */
    public function setBooleanValues(string $true, string $false): self
    {
        $this->booleanValues = [$false, $true];

        return $this;
    }

    /**
     * Specifies a value to implode the array with. Before performing this implode every element inside the array
     * is checked to be a string. If violating elements are found, an exception is thrown
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - key: The key of the value that was not a string
     * - culprit: The value of the index that wasn't a string
     *
     * @param string $delimiter
     * @param string $exceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function setImplodeOfArrays(
        string $delimiter,
        string $exceptionMessage = 'A value at index {key} was not of type string but of type {culprit.type}'
    ): self
    {
        $this->implode = [$delimiter, $exceptionMessage];

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        if (is_string($value)) {
            return $value;
        }

        switch (true) {
            case is_int($value):
                return (string) $value;
            case is_float($value):
                if (is_infinite($value) || is_nan($value)) break;

                return (string) $value;
            case is_bool($value):
                if ($this->booleanValues) {
                    return $this->booleanValues[$value];
                }

                return (string) $value;
            case is_array($value):
                if ($this->implode !== null) {
                    foreach ($value as $key => $element) {
                        if (!is_string($element)) {
                            throw new ParsingException(
                                $value,
                                Debug::parseMessage(
                                    $this->implode[1],
                                    [
                                        'value' => $value,
                                        'key' => $key,
                                        'culprit' => $element,
                                    ]
                                ),
                                $path
                            );
                        }
                    }

                    return implode($this->implode[0], $value);
                }
                break;
            case is_object($value):
                if (method_exists($value, '__toString')) {
                    return (string) $value;
                }
                break;
        }

        throw new ParsingException(
            $value,
            Debug::parseMessage(
                $this->typeExceptionMessage,
                [
                    'value' => $value,
                ]
            ),
            $path
        );
    }
}