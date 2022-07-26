<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Contract;

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
     * Creates a result builder for this subject
     *
     * @param string $description
     *
     * @return ResultBuilder
     */
    public function getResultBuilder(string $description): ResultBuilder;

    /**
     * @return bool
     */
    public function throwOnError(): bool;

    /**
     * @return null|Subject
     */
    public function getSourceSubject(): ?Subject;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return bool
     */
    public function isUtilitySubject(): bool;
}
