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

    private readonly bool $throwOnError;

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
        private readonly ?self  $sourceSubject,
        private readonly string $description,
        private readonly mixed $value,
        private readonly bool   $isUtilitySubject,
        ?bool                  $throwOnError
    )
    {
        $this->throwOnError = $throwOnError ?? $this->sourceSubject?->throwOnError() ?? true;
    }

    /**
     * Returns the default Subject to use
     *
     * @param mixed $value
     * @param string|null $description
     * @param bool $throwOnError
     *
     * @return Root
     */
    public static function default(mixed $value, ?string $description = null, bool $throwOnError = true): Root
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
    final public function getSubjectChain(bool $includeUtility = false): array
    {
        $return = [];
        if ($this->sourceSubject) {
            $return = $this->sourceSubject->getSubjectChain($includeUtility);
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
    public function getPathAsString(bool $includeUtility = false): string
    {
        return ltrim($this->concatPathStringParts($includeUtility), ' ');
    }

    /**
     * Concat every path string part so that the furthest subject (which is the one
     * this chain started with) is first and the other follow in order
     *
     * @param bool $includeUtility
     * @param bool $isLastInChain
     *
     * @return string
     */
    private function concatPathStringParts(bool $includeUtility, bool $isLastInChain = true): string
    {
        return (
                $this->sourceSubject?->concatPathStringParts($includeUtility, false)
                ?? ''
            ) .
            (
            $includeUtility || !$this->isUtilitySubject
                ? $this->getPathStringPart($isLastInChain)
                : ''
            );
    }

    /**
     * Returns the string representation of this path element
     *
     * @param bool $isLastInChain
     *
     * @return string
     */
    abstract protected function getPathStringPart(bool $isLastInChain): string;

    /**
     * Get the value of this subject
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Creates a result builder for this subject
     *
     * @param string $description
     *
     * @return ResultBuilder
     */
    public function getResultBuilder(string $description): ResultBuilder
    {
        return new ResultBuilder($this, $description);
    }

    /**
     * @return bool
     */
    public function throwOnError(): bool
    {
        return $this->throwOnError;
    }

    /**
     * @return Subject|null
     */
    public function getSourceSubject(): ?Subject
    {
        return $this->sourceSubject;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isUtilitySubject(): bool
    {
        return $this->isUtilitySubject;
    }
}
