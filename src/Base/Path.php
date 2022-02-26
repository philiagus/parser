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

use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Path\ArrayElement;
use Philiagus\Parser\Path\ArrayKey;
use Philiagus\Parser\Path\Chain;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Path\PropertyName;
use Philiagus\Parser\Path\PropertyValue;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Util\Debug;

abstract class Path
{

    private string $description;

    private ?Path $parent;

    private bool $isPathInValue;

    /**
     * Path constructor.
     *
     * @param string $description
     * @param Path|null $parent
     * @param bool $isPathInValue
     */
    public function __construct(string $description, self $parent = null, bool $isPathInValue = true)
    {
        $this->description = $description;
        $this->parent = $parent;
        $this->isPathInValue = $isPathInValue;
    }

    /**
     * Returns the default Path to use if no path is provided
     *
     * @return static
     */
    public static function default($value): self
    {
        return new Root(Debug::getType($value));
    }

    /**
     * @return string
     */
    final public function getDescription(): string
    {
        return $this->description;
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
     * @param bool $isPathInValue
     *
     * @return array
     */
    final public function flat(bool $isPathInValue = true): array
    {
        $return = [];
        if ($this->parent) {
            $return = $this->parent->flat($isPathInValue);
        }
        if (!$this->isPathInValue || $isPathInValue) {
            $return[] = $this;
        }

        return $return;
    }

    /**
     * Implementation of the magic __toString method, calls toString(false) internally
     *
     * @return string
     * @see Path::toString()
     */
    final public function __toString(): string
    {
        return $this->toString(false);
    }

    /**
     * Returns a string representation of the path, concatenating every level of  the path using the
     * delimiters defined in the paths
     *
     * @param bool $asValuePath
     *
     * @return string
     */
    final public function toString(bool $asValuePath = true): string
    {
        $result = '';
        if ($this->parent) {
            $result = $this->parent->toString($asValuePath);
        }

        if ($this->isPathInValue || !$asValuePath) {
            $result .= $this->getStringPart();
        }

        return ltrim($result, ' ');
    }

    /**
     * Returns the string representation of this path element
     *
     * @return string
     */
    abstract protected function getStringPart(): string;

    /**
     * Used when handing over the value of a property to another parser
     *
     * @param string $name
     * @param bool $isPathInValue
     *
     * @return PropertyValue
     */
    public function propertyValue(string $name, bool $isPathInValue = true): PropertyValue
    {
        return new PropertyValue($name, $this, $isPathInValue);
    }

    /**
     * Used when handing over meta information of a value such as the length of a string to another parser
     *
     * @param string $name
     * @param bool $isPathInValue
     *
     * @return MetaInformation
     */
    public function meta(string $name, bool $isPathInValue = true): MetaInformation
    {
        return new MetaInformation($name, $this, $isPathInValue);
    }

    /**
     * Used when handing over the value of a key of an array to another parser
     *
     * @param string $index
     * @param bool $isPathInValue
     *
     * @return ArrayElement
     */
    public function arrayElement(string $index, bool $isPathInValue = true): ArrayElement
    {
        return new ArrayElement($index, $this, $isPathInValue);
    }

    /**
     * Used when handing over the key of an array to another parser
     *
     * @param string $key
     * @param bool $isPathInValue
     *
     * @return ArrayKey
     */
    public function arrayKey(string $key, bool $isPathInValue = true): ArrayKey
    {
        return new ArrayKey($key, $this, $isPathInValue);
    }

    /**
     * Used when handing over the name of a property to another parser
     *
     * @param string $name
     * @param bool $isPathInValue
     *
     * @return PropertyName
     */
    public function propertyName(string $name, bool $isPathInValue = true): PropertyName
    {
        return new PropertyName($name, $this, $isPathInValue);
    }

    /**
     * Used when defining a parser as being chained and the result used after the parser
     *
     * @param string $description
     * @param bool $isPathInValue
     *
     * @return $this
     * @see Parser::getChainedPath()
     */
    public function chain(string $description, bool $isPathInValue = true): self
    {
        return new Chain($description, $this, $isPathInValue);
    }

}
