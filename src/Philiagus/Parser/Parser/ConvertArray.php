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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class ConvertArray extends Parser
{
    /**
     * The exception message thrown when the provided value is not an array
     *
     * @var string|null
     */
    private $typeExceptionMessage = 'Provided value is not an array';

    /**
     * @var callable[]
     */
    private $conversionList = [];

    /**
     * Defines the exception message to be thrown on type exception
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
     * @param $key
     * @param $forcedValue
     * @param ParserContract|null $andParse
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withDefaultedKey($key, $forcedValue, ParserContract $andParse = null): self
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
     * @param ParserContract $parser
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function withKey($key, ParserContract $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
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
     * Forwards each value to the specified parser and sets the value to the result of the parser
     *
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function withEachValue(ParserContract $parser): self
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
     * @param ParserContract $parser
     *
     * @param string $exceptionMessageOnInvalidArrayKey
     *
     * @return $this
     */
    public function withEachKey(
        ParserContract $parser,
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

    protected function execute($value, Path $path)
    {
        if (!is_array($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        $result = $value;
        foreach ($this->conversionList as $conversion) {
            $conversion($result, $path);
        }

        return $result;
    }
}