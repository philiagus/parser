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

abstract class Subject
{

    public readonly bool $throwOnError;

    /**
     * Path constructor.
     *
     * @param Subject|null $sourceSubject
     * @param string $description
     * @param mixed $value
     * @param bool $isUtilitySubject
     * @param bool $throwOnError
     */
    protected function __construct(
        public readonly ?self  $sourceSubject,
        public readonly string $description,
        private readonly mixed $value,
        public readonly bool   $isUtilitySubject,
        ?bool                  $throwOnError
    )
    {
        $this->throwOnError = $throwOnError ?? $this->sourceSubject?->throwOnError ?? true;
    }

    /**
     * Returns the default Path to use if no path is provided
     *
     * @return static
     */
    public static function default($value, ?string $description = null, bool $throwOnError = true): self
    {
        return new Root($value, $description, $throwOnError);
    }

    /**
     * Returns an array with the first element being the start of the path
     *
     * @param bool $includeUtility
     *
     * @return array
     */
    final public function flat(bool $includeUtility = false): array
    {
        $return = [];
        if ($this->sourceSubject) {
            $return = $this->sourceSubject->flat($includeUtility);
        }
        if ($includeUtility || !$this->isUtilitySubject) {
            $return[] = $this;
        }

        return $return;
    }

    /**
     * Returns a string representation of the path that lead to this current subject
     * if $includeUtility is true the path will also include utility subjects
     * created in the process. If false the result will generate a path string that hints to a
     * location in the originally provided source, such as "Array[0].name" for the name value of this
     * json: [{"name": "current location of the subject"}]
     *
     * @param bool $includeUtility
     *
     * @return string
     */
    final public function getPathAsString(bool $includeUtility = false): string
    {
        return ltrim($this->concatPathStringParts($includeUtility), ' ');
    }

    /**
     * Concat every path string part so that the furthest subject (which is the one
     * this chain started with) is first and the other follow in order
     *
     * @param $includeUtility
     *
     * @return string
     */
    private function concatPathStringParts($includeUtility): string
    {
        return ($this->sourceSubject?->concatPathStringParts($includeUtility) ?? '') .
            ($includeUtility || !$this->isUtilitySubject ? $this->getPathStringPart() : '');
    }

    /**
     * Returns the string representation of this path element
     *
     * @return string
     */
    abstract protected function getPathStringPart(): string;

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
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
