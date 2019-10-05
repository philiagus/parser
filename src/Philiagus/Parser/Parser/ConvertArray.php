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
use Philiagus\Parser\Type\AcceptsMixed;

class ConvertArray extends Parser implements AcceptsMixed
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

    public function convertNonArrays($usingArrayCast = true, $elementKey = null): self
    {
        if ($usingArrayCast) {
            $this->convertNonArrays = true;
        } else {
            if (!is_string($elementKey) && !is_int($elementKey)) {
                throw new ParserConfigurationException('Array key can only be string or integer');
            }

            $this->convertNonArrays = $elementKey;
        }

        return $this;
    }

    public function withDefaultedElement($key, $forcedValue): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->forcedKeys[$key] = $forcedValue;

        return $this;
    }

    public function withElementWhitelist($keys): self
    {
        foreach ($keys as $key) {
            if (!is_string($key) && !is_int($key)) {
                throw new ParserConfigurationException('Arrays only accept string or integer keys');
            }
        }

        $this->reduceToKeys = $keys;

        return $this;
    }

    public function withElement($key, AcceptsMixed $parser): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Arrays only accept string or integer keys');
        }

        $this->withElement[$key] = $parser;

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

        $keys = array_keys($value);
        foreach ($this->forcedKeys as $key => $element) {
            if (!in_array($key, $keys)) {
                $value[$key] = $element;
            }
        }

        foreach ($this->withElement as $key => $parser) {
            if (!in_array($key, $keys)) {
                throw new ParsingException($value, 'Array does not contain the requested key ' . var_export($key, true), $path);
            }

            $value[$key] = $parser->parse($value[$key], $path->index((string) $key));
        }

        return $value;
    }
}