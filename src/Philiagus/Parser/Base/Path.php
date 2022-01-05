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

use Philiagus\Parser\Path\ArrayElement;
use Philiagus\Parser\Path\ArrayKey;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Path\PropertyName;
use Philiagus\Parser\Path\PropertyValue;
use Philiagus\Parser\Path\Root;

abstract class Path
{

    private string $name;

    private ?Path $parent;

    /**
     * Path constructor.
     *
     * @param string $name
     * @param Path|null $parent
     */
    public function __construct(string $name, self $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    /**
     * Returns the default Path to use if no path is provided
     *
     * @return static
     */
    public static function default(): self
    {
        return new Root();
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the parent of this path or null if no parent is set
     *
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

    /**
     * Implementation of the magic __toString method, calls toString internally
     *
     * @return string
     * @see Path::toString()
     */
    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns a string representation of the path, concatenating every level of  the path using the
     * delimiters defined in the paths
     *
     * @return string
     */
    final public function toString(): string
    {
        if (!$this->parent) {
            return $this->name;
        }

        return $this->parent->toString() . $this->getDelimiter() . $this->name;
    }

    /**
     * Returns the delimiter used for this Path element.
     * Example: "parent->child" - in this case the "->" would be the delimiter of the child
     *
     * @return string
     */
    abstract protected function getDelimiter(): string;

    /**
     * Used when handing over the value of a property to another parser
     *
     * @param string $name
     *
     * @return PropertyValue
     */
    public function propertyValue(string $name): PropertyValue
    {
        return new PropertyValue($name, $this);
    }

    /**
     * Used when handing over meta information of a value such as the length of a string to another parser
     *
     * @param string $name
     *
     * @return MetaInformation
     */
    public function meta(string $name): MetaInformation
    {
        return new MetaInformation($name, $this);
    }

    /**
     * Used when handing over the value of a key of an array to another parser
     *
     * @param string $index
     *
     * @return ArrayElement
     */
    public function arrayElement(string $index): ArrayElement
    {
        return new ArrayElement($index, $this);
    }

    /**
     * Used when handing over the key of an array to another parser
     *
     * @param string $key
     *
     * @return ArrayKey
     */
    public function arrayKey(string $key): ArrayKey
    {
        return new ArrayKey($key, $this);
    }

    /**
     * Used when handing over the name of a property to another parser
     *
     * @param string $name
     *
     * @return PropertyName
     */
    public function propertyName(string $name): PropertyName
    {
        return new PropertyName($name, $this);
    }

}
