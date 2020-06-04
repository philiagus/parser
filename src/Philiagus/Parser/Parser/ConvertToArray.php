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
use Philiagus\Parser\Util\Debug;

class ConvertToArray extends Parser
{
    public const CONVERSION_DO_NOT_CONVERT = 0;
    public const CONVERSION_ARRAY_CAST = 1;
    public const CONVERSION_ARRAY_WITH_KEY = 2;

    private $typeExceptionMessage = 'Provided value is not an array';

    /**
     * @var bool|string|int
     */
    private $convertNonArrays = null;

    /**
     * @var string|int|null
     */
    private $convertNonArraysOption = null;

    /**
     * @var callable[]
     */
    private $conversionList = [];

    /**
     * Defines the exception message thrown when the input value is not an array and no conversion is active
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
     * Configures whether non-arrays should be converted to arrays
     * The $conversionType can be
     * - ConvertToArray::CONVERSION_DO_NOT_CONVERT
     *      -> no conversion will be done and a type exception is thrown
     * - ConvertToArray::CONVERSION_ARRAY_CAST
     *      -> a simple (array) cast will be performed
     * - ConvertToArray::CONVERSION_ARRAY_WITH_KEY
     *      -> an array will be created with key being the second parameter
     *
     * @param int $conversionType
     * @param string|int|null $option
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setConvertNonArrays(int $conversionType, $option = ''): self
    {
        if ($this->convertNonArrays !== null) {
            throw new ParserConfigurationException(
                'Configuration for conversion of non array cannot be overwritten'
            );
        }

        if (!in_array(
            $conversionType,
            [
                self::CONVERSION_DO_NOT_CONVERT,
                self::CONVERSION_ARRAY_CAST,
                self::CONVERSION_ARRAY_WITH_KEY,
            ]
        )) {
            throw new ParserConfigurationException(
                'Unknown conversion type was provided to setConvertNonArrays'
            );
        }

        if ($conversionType === self::CONVERSION_ARRAY_WITH_KEY) {
            if (!is_string($option) && !is_int($option)) {
                throw new ParserConfigurationException('Array key can only be string or integer');
            }
        }

        $this->convertNonArrays = $conversionType;
        $this->convertNonArraysOption = $option;

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

        $this->conversionList[] = function (array &$value, Path $path) use ($key, $forcedValue, $andParse) {
            $value += [$key => $forcedValue];
            if ($andParse) {
                $value[$key] = $andParse->parse($value[$key], $path->index((string) $key));
            }
        };

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
        $intersectList = [];
        foreach ($keys as $key) {
            if (!is_string($key) && !is_int($key)) {
                throw new ParserConfigurationException('Arrays only accept string or integer keys');
            }
            $intersectList[$key] = null;
        }

        $this->conversionList[] = function (array &$value) use ($intersectList) {
            $value = array_intersect_key($value, $intersectList);
        };

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * If the key does not exist an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - key: The key that was expected to be present
     *
     * @param $key
     * @param Parser $parser
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function withKey($key, Parser $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->conversionList[] = function (array &$value, Path $path) use ($key, $parser, $missingKeyExceptionMessage) {
            if (!array_key_exists($key, $value)) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($missingKeyExceptionMessage, ['key' => $key, 'value' => $value]),
                    $path
                );
            }

            $value[$key] = $parser->parse($value[$key], $path->index((string) $key));
        };

        return $this;
    }

    /**
     * @return $this
     */
    public function withSequentialKeys(): self
    {
        $this->conversionList[] = function (array &$value) {
            $value = array_values($value);
        };

        return $this;
    }

    /**
     * Forwards each value to the specified parser and overwrites the value with the result of the parser
     *
     * @param Parser $parser
     *
     * @return $this
     */
    public function withEachValue(Parser $parser): self
    {
        $this->conversionList[] = function (array &$value) use ($parser) {
            foreach ($value as &$item) {
                $item = $parser->parse($item);
            }
        };

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
        $this->conversionList[] = function (array &$value, Path $path) use ($parser, $exceptionMessageOnInvalidArrayKey) {
            $result = [];
            foreach ($value as $key => $item) {
                $newIndex = $parser->parse($key, $path->index((string) $key));
                if (!is_scalar($newIndex)) {
                    throw new ParserConfigurationException(
                        strtr(
                            $exceptionMessageOnInvalidArrayKey,
                            [
                                '{oldKey}' => $key,
                                '{newType}' => gettype($newIndex),
                            ]
                        )
                    );
                }
                $result[$newIndex] = $item;
            }

            $value = $result;
        };

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_array($value)) {
            switch ($this->convertNonArrays) {
                case self::CONVERSION_ARRAY_CAST:
                    $value = (array) $value;
                    break;
                case self::CONVERSION_ARRAY_WITH_KEY:
                    $value = [$this->convertNonArraysOption => $value];
                    break;
                default:
                    throw new ParsingException(
                        $value,
                        Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                        $path
                    );
            }
        }

        foreach ($this->conversionList as $conversion) {
            $conversion($value, $path);
        }

        return $value;
    }
}