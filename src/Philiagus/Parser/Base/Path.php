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

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Path\Index;
use Philiagus\Parser\Path\Key;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Path\Property;
use Philiagus\Parser\Path\PropertyName;

abstract class Path
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var self|null
     */
    private $parent;

    final public function __construct(string $name, self $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Path|null
     */
    final public function getParent(): ?Path
    {
        return $this->parent;
    }

    /**
     * Returns an array with the first element being the start of the path
     *
     * @return array
     */
    final public function flat(): array
    {
        if (!$this->parent) {
            return [$this];
        }

        $return = $this->parent->flat();
        $return[] = $this;

        return $return;
    }

    final public function __toString(): string
    {
        return $this->toString();
    }

    final public function toString(): string
    {
        if (!$this->parent) {
            return $this->name;
        }

        return $this->parent->toString() . $this->getDelimiter() . $this->name;
    }

    abstract protected function getDelimiter(): string;

    public function property(string $name): Property
    {
        return new Property($name, $this);
    }

    public function meta(string $name): MetaInformation
    {
        return new MetaInformation($name, $this);
    }

    public function index(string $index): Index
    {
        return new Index($index, $this);
    }

    public function key(string $key): Key
    {
        return new Key($key, $this);
    }

    public function propertyName(string $name): PropertyName
    {
        return new PropertyName($name, $this);
    }

}