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

interface Result extends Subject
{
    /**
     * Returns true if the parsing did not result in any errors
     * If false please use getErrors to get the list of errors that cause the lack of
     * success of this parser
     *
     * @return bool
     * @see hasErrors()
     */
    public function isSuccess(): bool;

    /**
     * Returns true if this result has been loaded with errors
     * If true it implies that this result was no success and thus
     * isSuccess will return false
     *
     * @return bool
     * @see isSuccess()
     */
    public function hasErrors(): bool;

    /**
     * Returns the result value of this result
     * This method has to throw a \LogicException if the Result is not a success
     *
     * @throws \LogicException
     */
    public function getValue(): mixed;

    /**
     * Returns the list of errors that are embedded in this result
     *
     * @return Error[]
     */
    public function getErrors(): array;
}
