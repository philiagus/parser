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
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withDefaultedElement(
        $key, $forcedValue,
        Parser $andParse = null,
        string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'
    ): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->forcedKeys[$key] = $forcedValue;
        if ($andParse) {
            $this->withKeyConvertingValue[$key] = [$andParse, $missingKeyExceptionMessage];
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
    public function withElement($key, Parser $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
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
            $keys = array_keys($value);
            /**
             * @var int|string $key
             * @var Parser $parser
             * @var string $exceptionMessage
             */
            foreach ($this->withKeyConvertingValue as $key => [$parser, $exceptionMessage]) {
                if (!in_array($key, $keys)) {
                    throw new ParsingException(
                        $value,
                        strtr($exceptionMessage, ['{key}' => var_export($key, true)]),
                        $path
                    );
                }

                $value[$key] = $parser->parse($value[$key], $path->index((string) $key));
            }
        }

        if ($this->sequentialKeys) {
            $value = array_values($value);
        }

        return $value;
    }
}