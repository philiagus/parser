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

class AssertArray
    extends Parser
{

    private $typeExceptionMessage = 'Provided value is not an array';

    /**
     * @var null|Parser
     */
    private $eachValue = null;

    /**
     * @var null|Parser
     */
    private $eachKey = null;

    /**
     * @var null|Parser
     */
    private $keys = null;

    /**
     * @var null|Parser
     */
    private $length = null;

    /**
     * @var array[]
     */
    private $withElement = [];

    /**
     * @var Parser[]
     */
    private $withDefaultedElement = [];

    /**
     * @var null|string
     */
    private $sequentialKeys = null;

    public function withTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * @param Parser $parser
     *
     * @return AssertArray
     */
    public function withEachValue(Parser $parser): self
    {
        $this->eachValue = $parser;

        return $this;
    }

    /**
     * @param Parser $parser
     *
     * @return $this
     */
    public function withEachKey(Parser $parser): self
    {
        $this->eachKey = $parser;

        return $this;
    }

    /**
     * @param Parser $arrayParser
     *
     * @return $this
     */
    public function withKeys(Parser $arrayParser): self
    {
        $this->keys = $arrayParser;

        return $this;
    }

    /**
     * @param Parser $integerParser
     *
     * @return $this
     */
    public function withLength(Parser $integerParser): self
    {
        $this->length = $integerParser;

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
    public function withKeyHavingValue($key, Parser $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->withElement[$key] = [$parser, $missingKeyExceptionMessage];

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
    public function withDefaultedElement($key, $default, Parser $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->withDefaultedElement[$key] = [$default, $parser];

        return $this;
    }

    /**
     * Specifies that this array is expected to have numeric keys starting at 0, incrementing by 1
     * @param string $exceptionMessage
     *
     * @return $this
     */
    public function withSequentialKeys(string $exceptionMessage = 'The array is not a sequential numerical array starting at 0'): self
    {
        $this->sequentialKeys = $exceptionMessage;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_array($value)) {
            throw new Exception\ParsingException($value, $this->typeExceptionMessage, $path);
        }

        if ($this->length) {
            $this->length->parse(count($value), $path->meta('length'));
        }

        if ($this->eachValue || $this->eachKey || $this->sequentialKeys) {
            $expectedSequenceKey = 0;
            foreach ($value as $index => $element) {
                if ($this->sequentialKeys && $index !== $expectedSequenceKey) {
                    throw new ParsingException($value, $this->sequentialKeys, $path);
                }
                if ($this->eachKey) $this->eachKey->parse($index, $path->key((string) $index));
                if ($this->eachValue) $this->eachValue->parse($element, $path->index((string) $index));
                $expectedSequenceKey++;
            }
        }

        $keys = array_keys($value);

        if($this->keys) {
            $this->keys->parse($keys, $path->meta('keys'));
        }

        /**
         * @var string|int $key
         * @var Parser $parser
         * @var string $exceptionMessage
         */
        foreach ($this->withElement as $key => [$parser, $exceptionMessage]) {
            if (!in_array($key, $keys)) {
                throw new ParsingException(
                    $value,
                    strtr($exceptionMessage, ['{key}' => var_export($key, true)]),
                    $path
                );
            }

            $parser->parse($value[$key], $path->index((string) $key));
        }

        foreach ($this->withDefaultedElement as $key => [$element, $parser]) {
            if (in_array($key, $keys)) {
                $element = $value[$key];
            }

            $parser->parse($element, $path->index((string) $key));
        }

        return $value;
    }
}