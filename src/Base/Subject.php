<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Base\Subject\Memory;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Root;

/**
 * Base class used by all subjects
 *
 * If you need to implement your own subject class for some reason, you can use this.
 *
 * But before you do this: Please check if there isn't a subject already present in the
 * default list of subjects that might fit your use case.
 *
 * @package Base
 */
readonly abstract class Subject implements Contract\MemoryProvider
{

    protected bool $throwOnError;

    protected Memory $memory;

    /**
     * Creates a new instance of the Subject
     * Please make sure that this method is called from every extending class via
     * parent::__construct(), providing the needed data
     *
     * If $throwOnError is not provided, the throwOnError setting of the $sourceSubject
     * is used. If no $sourceSubject is provided, throwOnError is set to TRUE by default.
     *
     * The setting $isUtilitySubject is only used to
     *
     * @param Subject|null $source The subject this subject is created from - this value is supposed to
     *                             be set for every subject with only exception being the Root subject
     *                             which is where the parsing start. Providing the $sourceSubject
     *                             allows for creation of a path identifying the location at which
     *                             an Error might have been encountered
     *
     * @param string $description A meaningful description of the subject. Please be aware that this description is
     *                            later wrapped into subject class specific string parts, configured via the
     *                            getPathStringPart() method
     *
     * @param mixed $value The value to be parsed
     *
     * @param bool $isUtility This value is used to identify whether this subject is added to the result of
     *                        getPathAsString() and getSubjectChain() or not. Essentially, utility subjects
     *                        are subjects that do not directly point at a location in the parsed value but
     *                        rather document the steps used in the parsing. Utility subjects should help
     *                        in debugging the parser chain used, non-utility subjects should help to
     *                        identify the location of the error in the provided subject.
     *
     * @param bool|null $throwOnError If $throwOnError is not provided, the throwOnError setting of the
     *                                $sourceSubject is used. If no $sourceSubject is provided,
     *                                $throwOnError is set to TRUE by default.
     */
    protected function __construct(
        protected ?Subject $source,
        protected string   $description,
        protected mixed    $value,
        protected bool     $isUtility,
        ?bool              $throwOnError
    )
    {
        $this->memory = $this->source?->getFullMemory() ?? new Subject\Memory();
        $this->throwOnError = $throwOnError ?? $this->source?->throwOnError() ?? true;
    }

    /** @inheritDoc */
    public function getFullMemory(): Memory
    {
        return $this->memory;
    }

    /**
     * Returns true if the subject wants parsers to throw a ParsingException on Error instead of
     * adding the error to the result
     *
     * @return bool
     * @see \Philiagus\Parser\Error
     * @see ParsingException
     * @see Result
     */
    public function throwOnError(): bool
    {
        return $this->throwOnError;
    }

    /**
     * Returns the default subject to use. This is a convenience function to easily create a Root subject
     * instance, which is the only subject that should be used on the entry point into the parsers.
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
        if ($this->source) {
            $return = $this->source->getSubjectChain($includeUtility);
        }
        if ($includeUtility || !$this->isUtility) {
            $return[] = $this;
        }

        return $return;
    }

    /**
     * Returns a string representation of the path that lead to this current subject
     * if $includeUtility is true the path will also include utility subjects
     * created in the process. If false the result will generate a path string that hints to a
     * location in the originally provided source, such as `Array[0].name` for the name value of this
     * json: [{"name": "current location of the subject"}]
     *
     * For a more json-style path the root object could have been created with `"$"` as its description.
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
                $this->source?->concatPathStringParts($includeUtility, false)
                ?? ''
            ) .
            (
            $includeUtility || !$this->isUtility
                ? $this->getPathStringPart($isLastInChain)
                : ''
            );
    }

    /**
     * Returns the string representation of this path element, which should always include the
     * description as provided by getDescription()
     *
     * @param bool $isLastInChain
     *
     * @return string
     * @see getDescription()
     *
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
     * Returns the subject this subject is based on, or NULL if this subject has no parent subject
     *
     * @return null|self
     */
    public function getSource(): ?Subject
    {
        return $this->source;
    }

    /**
     * Returns the string description of this subject. Please be aware
     * that this string description is the raw description. This description is
     * wrapped into a subject specific string when used in the context of the
     * getPathAsString() method
     *
     * @return string
     * @see getPathAsString()
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns true if this subject is a utility subject
     *
     * @return bool
     */
    public function isUtility(): bool
    {
        return $this->isUtility;
    }

    /** @inheritDoc */
    public function getMemory(object $of, mixed $default = null): mixed
    {
        return $this->memory->get($of, $default);
    }

    /** @inheritDoc */
    public function setMemory(object $of, mixed $value): void
    {
        $this->memory->set($of, $value);
    }

    /** @inheritDoc */
    public function hasMemory(object $of): bool
    {
        return $this->memory->has($of);
    }
}
