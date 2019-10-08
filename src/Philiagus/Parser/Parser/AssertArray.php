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
use Philiagus\Parser\Type;
use Philiagus\Parser\Type\AcceptsInteger;
use Philiagus\Parser\Type\AcceptsMixed;

class AssertArray
    extends Parser
    implements Type\AcceptsArray
{

    /**
     * @var null|AcceptsMixed
     */
    private $values = null;

    /**
     * @var null|AcceptsMixed
     */
    private $keys = null;

    /**
     * @var null|AcceptsInteger
     */
    private $length = null;

    /**
     * @var AcceptsMixed[]
     */
    private $withElement = [];

    /**
     * @var AcceptsMixed[]
     */
    private $withDefaultedElement = [];

    /**
     * @var bool
     */
    private $sequentialKeys = false;

    /**
     * @param AcceptsMixed $parser
     *
     * @return AssertArray
     */
    public function withValues(AcceptsMixed $parser): self
    {
        $this->values = $parser;

        return $this;
    }

    public function withKeys(AcceptsMixed $parser): self
    {
        $this->keys = $parser;

        return $this;
    }

    public function withLength(AcceptsInteger $parser): self
    {
        $this->length = $parser;

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

    public function withDefaultedElement($key, $default, AcceptsMixed $parser): self
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

        if ($this->values || $this->keys || $this->sequentialKeys) {
            $expectedSequenceKey = 0;
            foreach ($value as $index => $element) {
                if($this->sequentialKeys && $index !== $expectedSequenceKey) {
                    throw new ParsingException($value, 'The array is not a sequential numerical array starting at 0', $path);
                }
                if ($this->keys) $this->keys->parse($index, $path->key((string) $index));
                if ($this->values) $this->values->parse($element, $path->index((string) $index));
                $expectedSequenceKey++;
            }
        }

        $keys = array_keys($value);

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