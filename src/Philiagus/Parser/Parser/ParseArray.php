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

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class ParseArray extends AssertArray
{

    /**
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function modifyEachValue(ParserContract $parser): self
    {
        $this->assertionList[] = function (array $array, Path $path) use ($parser) {
            foreach ($array as $key => &$value) {
                $value = $parser->parse($value, $path->arrayElement((string) $key));
            }

            return $array;
        };

        return $this;
    }

    /**
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - oldKey: The key before parsing it
     * - newKey: The key after parsing
     *
     * @param ParserContract $parser
     * @param string $newKeyIsNotUseableMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function modifyEachKey(
        ParserContract $parser,
        string         $newKeyIsNotUseableMessage = 'A parser resulted in an invalid array key for key {oldKey.raw}'
    ): self
    {
        $this->assertionList[] = function (array $array, Path $path) use ($parser, $newKeyIsNotUseableMessage) {
            $result = [];
            foreach ($array as $key => $value) {
                $newKey = $parser->parse($key, $path->arrayKey((string) $key));
                if (!is_int($key) && !is_string($key)) {
                    throw new ParsingException(
                        $array,
                        Debug::parseMessage($newKeyIsNotUseableMessage, ['oldKey' => $key, 'newKey' => $newKey]),
                        $path
                    );
                }
                $result[$newKey] = $value;
            }

            return $result;
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
    public function modifyKeyValue($key, ParserContract $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
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

            $value[$key] = $parser->parse($value[$key], $path->arrayElement((string) $key));

            return $value;
        };

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function defaultKey($key, $value): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function (array $array, Path $path) use ($key, $value) {
            if (!array_key_exists($key, $array)) {
                $array[$key] = $value;
            }

            return $array;
        };

        return $this;
    }

    /**
     * @param array $array
     *
     * @return $this
     */
    public function unionWith(array $array): self
    {
        $this->assertionList[] = function (array $value) use ($array) {
            $value += $array;

            return $value;
        };

        return $this;
    }

    /**
     * Forces the array to have sequential keys, this is identical to calling array_values on the array
     *
     * @return $this
     */
    public function forceSequentialKeys(): self
    {
        $this->assertionList[] = function (array $value, Path $path) {
            return array_values($value);
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
    public function modifyOptionalKeyValue($key, ParserContract $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function (array $value, Path $path) use ($key, $parser) {
            if (array_key_exists($key, $value)) {
                $value[$key] = $parser->parse($value[$key], $path->arrayElement((string) $key));
            }

            return $value;
        };

        return $this;
    }
}
