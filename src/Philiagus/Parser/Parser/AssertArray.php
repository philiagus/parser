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
     * @var Parser[]
     */
    private $withElement = [];

    /**
     * @var Parser[]
     */
    private $withDefaultedElement = [];

    /**
     * @var bool
     */
    private $sequentialKeys = false;

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

    public function withEachKey(Parser $parser): self
    {
        $this->eachKey = $parser;

        return $this;
    }

    public function withKeys(Parser $arrayParser): self
    {
        $this->keys = $arrayParser;

        return $this;
    }

    public function withLength(Parser $integerParser): self
    {
        $this->length = $integerParser;

        return $this;
    }

    /**
     * @param $key
     * @param Parser $parser
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withKeyHavingValue($key, Parser $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->withElement[$key] = $parser;

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
            throw new Exception\ParsingException($value, 'Provided value is not an array', $path);
        }

        if ($this->length) {
            $this->length->parse(count($value), $path->meta('length'));
        }

        if ($this->eachValue || $this->eachKey || $this->sequentialKeys) {
            $expectedSequenceKey = 0;
            foreach ($value as $index => $element) {
                if ($this->sequentialKeys && $index !== $expectedSequenceKey) {
                    throw new ParsingException($value, 'The array is not a sequential numerical array starting at 0', $path);
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

        foreach ($this->withElement as $key => $parser) {
            if (!in_array($key, $keys)) {
                throw new ParsingException($value, 'Array does not contain the requested key ' . var_export($key, true), $path);
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