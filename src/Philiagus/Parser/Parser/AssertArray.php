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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use SplStack;

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
     * @param Parser $parser
     *
     * @return $this
     */
    public function withEachValue(Parser $parser): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($parser) {
            foreach ($value as $index => $element) {
                $parser->parse($element, $path->index((string) $index));
            }
        };

        return $this;
    }

    /**
     * @param Parser $parser
     *
     * @return $this
     */
    public function withEachKey(Parser $parser): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($parser) {
            foreach ($keys as $key) {
                $parser->parse($key, $path->key((string) $key));
            }
        };

        return $this;
    }

    /**
     * @param Parser $arrayParser
     *
     * @return $this
     */
    public function withKeys(Parser $arrayParser): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($arrayParser) {
            $arrayParser->parse($keys, $path->meta('keys'));
        };

        return $this;
    }

    /**
     * @param Parser $integerParser
     *
     * @return $this
     */
    public function withLength(Parser $integerParser): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($integerParser) {
            $integerParser->parse(count($value), $path->meta('length'));
        };

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

        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($key, $parser, $missingKeyExceptionMessage) {
            if (!array_key_exists($key, $value)) {
                throw new ParsingException(
                    $value,
                    strtr($missingKeyExceptionMessage, ['{key}' => var_export($key, true)]),
                    $path
                );
            }
            $parser->parse($value[$key], $path->index((string) $key));
        };

        return $this;
    }

    /**
     * @param $key
     * @param $default
     * @param Parser $parser
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withDefaultedKey($key, $default, Parser $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($key, $default, $parser) {
            if (in_array($key, $keys)) {
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
     * @param string $exceptionMessage
     *
     * @return $this
     */
    public function withSequentialKeys(string $exceptionMessage = 'The array is not a sequential numerical array starting at 0'): self
    {
        $this->assertionList[] = function (array $value, array $keys, Path $path) use ($exceptionMessage) {
            $assumedKey = 0;
            foreach (array_keys($value) as $key) {
                if ($key !== $assumedKey) {
                    throw new ParsingException($value, $exceptionMessage, $path);
                }
                $assumedKey++;
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
            throw new Exception\ParsingException($value, $this->typeExceptionMessage ?? self::DEFAULT_TYPE_EXCEPTION_MESSAGE, $path);
        }

        $keys = array_keys($value);
        foreach ($this->assertionList as $parser) {
            $parser($value, $keys, $path);
        }

        return $value;
    }
}