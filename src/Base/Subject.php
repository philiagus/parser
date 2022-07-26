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

use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\Root;

abstract class Subject implements Contract\Subject
{

    private readonly bool $throwOnError;

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
     * @param Contract\Subject|null $sourceSubject The subject this subject is created from - this value is supposed to
     *                                             be set for every subject with only exception being the Root subject
     *                                             which is where the parsing start. Providing the $sourceSubject
     *                                             allows for creation of a path identifying the location at which
     *                                             an Error might have been encountered
     *
     * @param string $description A meaningful description of the subject. Please be aware that this description is
     *                            later wrapped into subject class specific string parts, configured via the
     *                            getPathStringPart() method
     *
     * @param mixed $value The value to be parsed
     *
     * @param bool $isUtilitySubject This value is used to identify whether this subject is added to the result of
     *                               getPathAsString() and getSubjectChain() or not. Essentially, utility subjects
     *                               are subjects that do not directly point at a location in the parsed value but
     *                               rather document the steps used in the parsing. Utility subjects should help
     *                               in debugging the parser chain used, non-utility subjects should help to
     *                               identify the location of the error in the provided subject.
     *
     * @param bool|null $throwOnError If $throwOnError is not provided, the throwOnError setting of the
     *                                $sourceSubject is used. If no $sourceSubject is provided,
     *                                $throwOnError is set to TRUE by default.
     */
    protected function __construct(
        private readonly ?Contract\Subject $sourceSubject,
        private readonly string            $description,
        private readonly mixed             $value,
        private readonly bool              $isUtilitySubject,
        ?bool                              $throwOnError
    )
    {
        $this->throwOnError = $throwOnError ?? $this->sourceSubject?->throwOnError() ?? true;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getResultBuilder(string $description): ResultBuilder
    {
        return new ResultBuilder($this, $description);
    }

    /**
     * @inheritDoc
     */
    public function getSourceSubject(): ?Contract\Subject
    {
        return $this->sourceSubject;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function isUtilitySubject(): bool
    {
        return $this->isUtilitySubject;
    }
}
