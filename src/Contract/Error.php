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

interface Error
{

    /**
     * Returns the message of this error
     *
     * @return string
     */
    public function getMessage(): string;


    /**
     * Return the throwable that lead to the creation of this Error (if any)
     *
     * @return \Throwable|null
     */
    public function getSourceThrowable(): ?\Throwable;


    /**
     * Throws the error as a ParsingException
     *
     * @return never
     * @throws ParsingException
     */
    public function throw(): never;

    /**
     * Returns the subject which was parsed when this error occurred
     *
     * @return Subject
     */
    public function getSubject(): Subject;

    /**
     * Returns the list of errors that lead to this Error
     * This array can be empty
     *
     * @return self[]
     */
    public function getSourceErrors(): array;

    /**
     * Returns true if this error has a list of errors that lead to it
     *
     * @return bool
     */
    public function hasSourceErrors(): bool;

    /**
     * Return true if the error has been loaded with an Exception that caused this error
     *
     * @return bool
     */
    public function hasSourceThrowable(): bool;

    /**
     * Calls getPathAsString($includeUtility) on the subject of this error
     *
     * @param bool $includeUtility
     *
     * @return string
     * @see Subject::getPathAsString()
     */
    public function getPathAsString(bool $includeUtility = false): string;

}
