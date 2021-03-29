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
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertArray
    extends Parser
{
    /**
     * The exception message thrown when the provided value is not an array
     *
     * @var string|null
     */
    private $typeExceptionMessage = 'Provided value is not an array';

    /**
     * List of assertions to be performed in order
     *
     * @var callable[]
     */
    private $assertionList = [];

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
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function withEachValue(ParserContract $parser): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($parser) {
            foreach ($value as $index => $element) {
                $parser->parse($element, $path->index((string) $index));
            }
        };

        return $this;
    }

    /**
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function withEachKey(ParserContract $parser): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($parser) {
            foreach ($keys as $key) {
                $parser->parse($key, $path->key((string) $key));
            }
        };

        return $this;
    }

    /**
     * @param ParserContract $arrayParser
     *
     * @return $this
     */
    public function withKeys(ParserContract $arrayParser): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($arrayParser) {
            $arrayParser->parse($keys, $path->meta('keys'));
        };

        return $this;
    }

    /**
     * Defines a parser that the number of elements in the array gets forwarded to
     *
     * @param ParserContract $integerParser
     *
     * @return $this
     */
    public function withLength(ParserContract $integerParser): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($integerParser) {
            $integerParser->parse(count($value), $path->meta('length'));
        };

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * If the key does not exist an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - key: The missing key
     * - value: The value currently being parsed
     *
     *
     * @param $key
     * @param ParserContract $parser
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     * @see Debug::parseMessage()
     */
    public function withKey($key, ParserContract $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($key, $parser, $missingKeyExceptionMessage) {
            if (!array_key_exists($key, $value)) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($missingKeyExceptionMessage, ['key' => $key, 'value' => $value,]),
                    $path
                );
            }
            $parser->parse($value[$key], $path->index((string) $key));
        };

        return $this;
    }

    /**
     * Performs a parser on the value of a key or the $default if the given key does not exist
     * in the array
     *
     * @param $key
     * @param $default
     * @param ParserContract $parser
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withDefaultedKey($key, $default, ParserContract $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($key, $default, $parser) {
            if (array_key_exists($key, $value)) {
                $element = $value[$key];
            } else {
                $element = $default;
            }

            $parser->parse($element, $path->index((string) $key));
        };

        return $this;
    }

    /**
     * Specifies that this array is expected to have numeric keys starting at 0, incrementing by 1
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $exceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function withSequentialKeys(string $exceptionMessage = 'The array is not a sequential numerical array starting at 0'): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($exceptionMessage) {
            $assumedKey = 0;
            foreach ($keys as $key) {
                if ($key !== $assumedKey) {
                    throw new ParsingException($value, Debug::parseMessage($exceptionMessage, ['value' => $value]), $path);
                }
                $assumedKey++;
            }
        };

        return $this;
    }

    /**
     * If the array has the provided key, the value of that key is provided to the parser
     *
     * @param $key
     * @param ParserContract $parser
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withOptionalKey($key, ParserContract $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function(array $value, array $keys, Path $path) use ($key, $parser) {
            if(array_key_exists($key, $value)) {
                $parser->parse($value[$key], $path->index((string) $key));
            }
        };

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_array($value)) {
            throw new Exception\ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        $keys = array_keys($value);
        foreach ($this->assertionList as $parser) {
            $parser($value, $keys, $path);
        }

        return $value;
    }
}