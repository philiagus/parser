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

use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\Root;
use Philiagus\Parser\Util\Debug;

abstract class Subject
{

    /**
     * Path constructor.
     *
     * @param mixed $value
     * @param string $pathDescription
     * @param Subject|null $parent
     * @param bool $isPathInValue
     * @param bool $throwOnError
     */
    public function __construct(
        private readonly mixed  $value,
        private readonly string $pathDescription,
        private readonly ?self  $parent = null,
        private readonly bool   $isPathInValue = true,
        private readonly bool   $throwOnError = true
    )
    {
    }

    /**
     * Returns the default Path to use if no path is provided
     *
     * @return static
     */
    public static function default($value, bool $throwOnError = true): self
    {
        return new Root($value, Debug::getType($value), throwOnError: $throwOnError);
    }

    /**
     * @return string
     */
    final public function getPathDescription(): string
    {
        return $this->pathDescription;
    }

    /**
     * Returns the parent of this path or null if no parent is set
     *
     * @return Subject|null
     */
    final public function getParent(): ?Subject
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
     * Returns a string representation of the path, concatenating every level of  the path using the
     * delimiters defined in the paths
     *
     * @param bool $asValuePath
     *
     * @return string
     */
    final public function getPathAsString(bool $asValuePath = true): string
    {
        $result = '';
        if ($this->parent) {
            $result = $this->parent->getPathAsString($asValuePath);
        }

        if ($this->isPathInValue || !$asValuePath) {
            $result .= $this->getPathStringPart();
        }

        return ltrim($result, ' ');
    }

    /**
     * Returns the string representation of this path element
     *
     * @return string
     */
    abstract protected function getPathStringPart(): string;

    /**
     * @return bool
     */
    public function isPathInValue(): bool
    {
        return $this->isPathInValue;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function throwOnError(): bool
    {
        return $this->throwOnError;
    }

    /**
     * @param string $description
     *
     * @return ResultBuilder
     */
    public function getResultBuilder(string $description): ResultBuilder
    {
        return new ResultBuilder($this, $description);
    }

}
