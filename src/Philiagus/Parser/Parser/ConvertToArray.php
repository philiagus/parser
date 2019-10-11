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
    private $withElement = [];

    /**
     * @var bool
     */
    private $sequentialKeys = false;

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
    public function withDefaultedElement($key, $forcedValue, Parser $andParse = null): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->forcedKeys[$key] = $forcedValue;
        if ($andParse) {
            $this->withElement[$key] = $andParse;
        }

        return $this;
    }

    /**
     * @param array $keys
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withElementWhitelist(array $keys): self
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
     * @param $key
     * @param Parser $parser
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withElement($key, Parser $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->withElement[$key] = $parser;

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
                throw new ParsingException($value, 'Value is not of type array and array casting not active', $path);
            } else {
                $value = [$this->convertNonArrays => $value];
            }
        }

        if ($this->reduceToKeys !== null) {
            $value = array_intersect_key($value, array_flip($this->reduceToKeys));
        }

        if($this->forcedKeys) {
            $value += $this->forcedKeys;
        }

        if($this->withElement) {
            $keys = array_keys($value);
            foreach ($this->withElement as $key => $parser) {
                if (!in_array($key, $keys)) {
                    throw new ParsingException($value, 'Array does not contain the requested key ' . var_export($key, true), $path);
                }

                $value[$key] = $parser->parse($value[$key], $path->index((string) $key));
            }
        }

        if($this->sequentialKeys) {
            $value = array_values($value);
        }

        return $value;
    }
}