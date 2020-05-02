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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;

class ConvertToString extends Parser
{

    private $typeExceptionMessage = 'Variable of type {type} could not be converted to a string';

    /**
     * @var array|null
     */
    private $booleanValues = null;

    /**
     * @var null|string[]
     */
    private $implode = null;

    /**
     * Available replacers:
     * {type} = gettype of the provided variable
     *
     * @param string $message
     *
     * @return $this
     */
    public function overwriteTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * @param string $true
     * @param string $false
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setBooleanValues(string $true, string $false): self
    {
        if($this->booleanValues !== null) {
            throw new ParserConfigurationException(
                'Already set boolean value conversion configuration of ConvertToString cannot be overwritten'
            );
        }

        $this->booleanValues = [$false, $true];

        return $this;
    }

    /**
     * Specifies a value to implode the array with. Before performing this implode every element inside the array
     * is checked to be a string. If non strings are found an exception is thrown
     * Exception replacers:
     * {index} = The index of the value that wan't a string
     * {type} = Thy gettype of the value that wasn't a string
     *
     * @param string $delimiter
     * @param string $exceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setImplodeOfArrays(
        string $delimiter,
        string $exceptionMessage = 'A value at index {index} was not of type string but of type {type}'
    ): self
    {
        if($this->implode !== null) {
            throw new ParserConfigurationException(
                'Already set implode configuration of ConvertToString cannot be overwritten'
            );
        }
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
                break;
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
                                strtr(
                                    $this->implode[1],
                                    [
                                        '{key}' => var_export($key, true),
                                        '{type}' => gettype($element),
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
            strtr(
                $this->typeExceptionMessage,
                [
                    '{type}' => gettype($value),
                ]
            ),
            $path
        );
    }
}