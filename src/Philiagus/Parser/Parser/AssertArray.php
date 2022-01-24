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

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverridableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Util\Debug;

class AssertArray implements Parser
{
    use Chainable, OverridableChainDescription;

    /** @var callable[] */
    protected array $assertionList = [];

    /** @var string */
    private string $typeExceptionMessage = 'Provided value is not an array';

    protected function __construct()
    {
    }

    /**
     * @return static
     */
    public static function new()
    {
        return new static();
    }

    /**
     * Defines the exception message to use if the value is not a string
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

    public function giveEachValue(ParserContract $parser): self
    {
        $this->assertionList[] = function (array $array, Path $path) use ($parser) {
            foreach ($array as $key => $value) {
                $parser->parse($value, $path->arrayElement((string) $key));
            }

            return $array;
        };

        return $this;
    }

    public function parse($value, ?Path $path = null)
    {
        if (!is_array($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        $path = $path ?? new Root();
        foreach ($this->assertionList as $assertion) {
            $value = $assertion($value, $path);
        }

        return $value;
    }

    /**
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveEachKey(ParserContract $parser): self
    {
        $this->assertionList[] = function (array $value, Path $path) use ($parser) {
            foreach ($value as $key => $_) {
                $parser->parse($key, $path->arrayKey((string) $key));
            }

            return $value;
        };

        return $this;
    }

    /**
     * @param ParserContract $arrayParser
     *
     * @return $this
     */
    public function giveKeys(ParserContract $arrayParser): self
    {
        $this->assertionList[] = function (array $array, Path $path) use ($arrayParser) {
            $arrayParser->parse(array_keys($array), $path->meta('keys'));

            return $array;
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
    public function giveLength(ParserContract $integerParser): self
    {
        $this->assertionList[] = function (array $value, Path $path) use ($integerParser) {
            $integerParser->parse(count($value), $path->meta('length'));

            return $value;
        };

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * If the key does not exist an exception with the specified message is thrown
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
    public function giveKeyValue($key, ParserContract $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function (array $value, Path $path) use ($key, $parser, $missingKeyExceptionMessage) {
            if (!array_key_exists($key, $value)) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($missingKeyExceptionMessage, ['key' => $key, 'value' => $value,]),
                    $path
                );
            }
            $parser->parse($value[$key], $path->arrayElement((string) $key));

            return $value;
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
    public function giveKeyValueDefaulted($key, $default, ParserContract $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function (array $value, Path $path) use ($key, $default, $parser) {
            if (array_key_exists($key, $value)) {
                $parser->parse($value[$key], $path->arrayElement((string) $key));
            } else {
                $parser->parse($default, $path->arrayElement((string) $key));
            }
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
    public function assertSequentialKeys(string $exceptionMessage = 'The array is not a sequential numerical array starting at 0'): self
    {
        $this->assertionList[] = function (array $value, Path $path) use ($exceptionMessage) {
            $assumedKey = 0;
            foreach (array_keys($value) as $key) {
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
    public function giveOptionalKeyValue($key, ParserContract $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function (array $value, Path $path) use ($key, $parser) {
            if (array_key_exists($key, $value)) {
                $parser->parse($value[$key], $path->arrayElement((string) $key));
            }

            return $value;
        };

        return $this;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert array');
    }
}
