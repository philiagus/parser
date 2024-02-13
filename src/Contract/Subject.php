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

namespace Philiagus\Parser\Contract;

use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\ResultBuilder;

interface Subject
{
    /**
     * Returns an array with the first element being the start of the path
     *
     * @param bool $includeUtility
     *
     * @return array
     */
    public function getSubjectChain(bool $includeUtility = false): array;

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
    public function getPathAsString(bool $includeUtility = false): string;

    /**
     * Get the value of this subject
     *
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Returns true if the subject wants parsers to throw a ParsingException on Error instead of
     * adding the error to the result
     *
     * @return bool
     * @see Error
     * @see ParsingException
     * @see Result
     */
    public function throwOnError(): bool;

    /**
     * Returns the subject this subject is based on, or NULL if this subject has no parent subject
     *
     * @return null|Subject
     */
    public function getSourceSubject(): ?Subject;

    /**
     * Returns the string description of this subject. Please be aware
     * that this string description is the raw description. This description is
     * wrapped into a subject specific string when used in the context of the
     * getPathAsString() method
     *
     * @return string
     * @see getPathAsString()
     */
    public function getDescription(): string;

    /**
     * Returns true if this subject is a utility subject
     *
     * @return bool
     */
    public function isUtilitySubject(): bool;

    /**
     * Returns the root subject object and thus can be used to differentiate between
     * parser runs as every new run should be started with a new subject.
     * @return self
     */
    public function getRoot(): self;
}
