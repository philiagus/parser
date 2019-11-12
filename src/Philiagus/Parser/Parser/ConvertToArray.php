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

class ConvertToArray extends Parser
{

    private $typeExceptionMessage = 'Provided value is not an array';

    /**
     * @var bool|string|int
     */
    private $convertNonArrays = false;

    /**
     * @var array
     */
    private $forcedKeys = [];

    /**
     * @var array
     */
    private $reduceToKeys = null;

    /**
     * @var array
     */
    private $withKeyConvertingValue = [];

    /**
     * @var bool
     */
    private $sequentialKeys = false;

    /**
     * @var null|mixed[]
     */
    private $eachKey = null;

    /**
     * @var null|Parser
     */
    private $eachValue = null;

    /**
     * Defines the exception message thrown when the input value is not an array and no conversion is active
     *
     * @param string $message
     *
     * @return $this
     */
    public function withTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * @return $this
     */
    public function convertNonArraysWithArrayCast(): self
    {
        $this->convertNonArrays = true;

        return $this;
    }

    /**
     * @param $key
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function convertNonArraysWithKey($key): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Array key can only be string or integer');
        }

        $this->convertNonArrays = $key;

        return $this;
    }

    /**
     * @param $key
     * @param $forcedValue
     * @param Parser|null $andParse
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withDefaultedKey($key, $forcedValue, Parser $andParse = null): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->forcedKeys[$key] = $forcedValue;
        if ($andParse) {
            $this->withKeyConvertingValue[$key] = [$andParse, null];
        }

        return $this;
    }

    /**
     * @param array $keys
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withKeyWhitelist(array $keys): self
    {
        foreach ($keys as $key) {
            if (!is_string($key) && !is_int($key)) {
                throw new ParserConfigurationException('Arrays only accept string or integer keys');
            }
        }

        $this->reduceToKeys = $keys;

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * If the key does not exist an exception with the specified message is thrown.
     * Replacers in the exception message:
     * {key} = var_export($key, true)
     *
     * @param $key
     * @param Parser $parser
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withKey($key, Parser $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->withKeyConvertingValue[$key] = [$parser, $missingKeyExceptionMessage];

        return $this;
    }

    /**
     * @return $this
     */
    public function withSequentialKeys(): self
    {
        $this->sequentialKeys = true;

        return $this;
    }

    /**
     * Forwards each value to the specified parser and overwrites the value with the result of the parser
     * @param Parser $parser
     *
     * @return $this
     */
    public function withEachValue(Parser $parser): self
    {
        $this->eachValue = $parser;

        return $this;
    }

    /**
     * Forwards each of the keys to the specified parser and changes the key to the return value of the parser
     * The return value of the parser must be a scalar value. If not, the provided exception message
     * will be thrown as ParserConfigurationException
     * If two keys are the same the last value is preserved but ordered at the first occurrence of the key
     *
     * Replacers in the exception message:
     * {oldKey} = var_export($key, true)
     * {newType} = gettype of the new key
     *
     *
     * @param Parser $parser
     *
     * @param string $exceptionMessageOnInvalidArrayKey
     *
     * @return $this
     */
    public function withEachKey(
        Parser $parser,
        string $exceptionMessageOnInvalidArrayKey = 'The index {oldKey} was converted by a parser to a value of type {newType} not supported as an array index'
    ): self
    {
        $this->eachKey = [$parser, $exceptionMessageOnInvalidArrayKey];

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_array($value)) {
            if ($this->convertNonArrays === true) {
                $value = (array) $value;
            } elseif ($this->convertNonArrays === false) {
                throw new ParsingException($value, $this->typeExceptionMessage, $path);
            } else {
                $value = [$this->convertNonArrays => $value];
            }
        }

        if ($this->reduceToKeys !== null) {
            $value = array_intersect_key($value, array_flip($this->reduceToKeys));
        }

        if ($this->forcedKeys) {
            $value += $this->forcedKeys;
        }

        if ($this->withKeyConvertingValue) {
            /**
             * @var int|string $key
             * @var Parser $parser
             * @var string|null $exceptionMessage
             */
            foreach ($this->withKeyConvertingValue as $key => [$parser, $exceptionMessage]) {
                if ($exceptionMessage !== null && !array_key_exists($key, $value)) {
                    throw new ParsingException(
                        $value,
                        strtr($exceptionMessage, ['{key}' => var_export($key, true)]),
                        $path
                    );
                }

                $value[$key] = $parser->parse($value[$key], $path->index((string) $key));
            }
        }

        if ($this->eachKey || $this->eachValue) {
            $newValue = [];
            /** @var Parser|null $eachKeyParser */
            $eachKeyParser = null;
            $eachKeyExceptionMessage = null;
            if($this->eachKey) {
                [$eachKeyParser, $eachKeyExceptionMessage] = $this->eachKey;
            }
            foreach ($value as $index => $element) {
                if ($eachKeyParser) {
                    $newIndex = $eachKeyParser->parse($index, $path->index((string) $index));
                    if(!is_scalar($newIndex)) {
                        throw new ParserConfigurationException(
                            strtr(
                                $eachKeyExceptionMessage,
                                [
                                    '{oldKey}' => $index,
                                    '{newType}' => gettype($newIndex)
                                ]
                            )
                        );
                    }
                } else {
                    $newIndex = $index;
                }
                if ($this->eachValue) {
                    $newElement = $this->eachValue->parse($element, $path->index((string) $index));
                } else {
                    $newElement = $element;
                }
                $newValue[$newIndex] = $newElement;
            }

            $value = $newValue;
            unset($newValue);
        }

        if ($this->sequentialKeys) {
            $value = array_values($value);
        }

        return $value;
    }
}