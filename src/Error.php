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

namespace Philiagus\Parser;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Stringify;

/**
 * Instances of this class represent an error that was found during parsing of values.
 *
 * Errors always contain the subject that they acted on. As subjects always contain the entire chain that lead
 * to the subject this means that errors also always contain the path that lead to the error.
 *
 * In `throw mode` errors are usually directly thrown as ParsingExceptions
 *
 * @package Error
 * @see ParsingException
 */
readonly class Error
{

    /**
     * @param Subject $subject
     * @param string $message
     * @param \Throwable|null $sourceThrowable
     * @param array $sourceErrors
     */
    public function __construct(
        private Subject     $subject,
        private string      $message,
        private ?\Throwable $sourceThrowable = null,
        private array       $sourceErrors = []
    )
    {
        foreach ($sourceErrors as $sourceError) {
            if (!$sourceError instanceof Error) {
                throw new \LogicException(
                    "Error class has been filled with non-Error instance as sourceError: " . Stringify::stringify($sourceError)
                );
            }
        }
    }

    /**
     * Creates the error using Stringify::parseMessage with $message and $replacers
     * The value of the subject will by default be provided as a 'value' replacer target
     * So you can use {value} as a replacer in all calls to this method
     *
     * @param Subject $subject
     * @param string $message
     * @param array $replacers
     * @param \Throwable|null $sourceThrowable
     * @param array $sourceErrors
     *
     * @return static
     * @see Stringify::parseMessage()
     *
     */
    public static function createUsingStringify(
        Subject     $subject,
        string      $message,
        array       $replacers = [],
        ?\Throwable $sourceThrowable = null,
        array       $sourceErrors = []
    ): static
    {
        return new static(
            $subject,
            Stringify::parseMessage($message, $replacers + ['value' => $subject->getValue()]),
            $sourceThrowable,
            $sourceErrors
        );
    }

    /**
     * Returns the message of this error
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Throws the error as a ParsingException
     *
     * @return never
     * @throws ParsingException
     */
    public function throw(): never
    {
        throw new ParsingException($this);
    }

    /**
     * Returns the subject which was parsed when this error occurred
     *
     * @return Subject
     */
    public function getSubject(): Subject
    {
        return $this->subject;
    }

    /**
     * Return the throwable that lead to the creation of this Error (if any)
     *
     * @return \Throwable|null
     */
    public function getSourceThrowable(): ?\Throwable
    {
        return $this->sourceThrowable;
    }

    /**
     * Returns the list of errors that lead to this Error
     * This array can be empty
     *
     * @return self[]
     */
    public function getSourceErrors(): array
    {
        return $this->sourceErrors;
    }

    /**
     * Returns true if this error has a list of errors that lead to it
     *
     * @return bool
     */
    public function hasSourceErrors(): bool
    {
        return !empty($this->sourceErrors);
    }

    /**
     * Return true if the error has been loaded with an Exception that caused this error
     *
     * @return bool
     */
    public function hasSourceThrowable(): bool
    {
        return isset($this->sourceThrowable);
    }

    /**
     * Calls getPathAsString($includeUtility) on the subject of this error
     *
     * @param bool $includeUtility
     *
     * @return string
     * @see Subject::getPathAsString
     */
    public function getPathAsString(bool $includeUtility = false): string
    {
        return $this->subject->getPathAsString($includeUtility);
    }
}
