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
readonly class Error implements Contract\Error
{

    /**
     * @param Contract\Subject $subject
     * @param string $message
     * @param \Throwable|null $sourceThrowable
     * @param array $sourceErrors
     */
    public function __construct(
        private Contract\Subject $subject,
        private string           $message,
        private ?\Throwable      $sourceThrowable = null,
        private array            $sourceErrors = []
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
     * @param Contract\Subject $subject
     * @param string $message
     * @param array $replacers
     * @param \Throwable|null $sourceThrowable
     * @param array $sourceErrors
     *
     * @return Error
     * @see Stringify::parseMessage()
     *
     */
    public static function createUsingStringify(
        Contract\Subject $subject,
        string           $message,
        array            $replacers = [],
        ?\Throwable      $sourceThrowable = null,
        array            $sourceErrors = []
    ): Error
    {
        return new Error(
            $subject,
            Stringify::parseMessage($message, $replacers + ['value' => $subject->getValue()]),
            $sourceThrowable,
            $sourceErrors
        );
    }

    /** @inheritDoc */
    #[\Override] public function getMessage(): string
    {
        return $this->message;
    }

    /** @inheritDoc */
    #[\Override] public function throw(): never
    {
        throw new ParsingException($this);
    }

    /** @inheritDoc */
    #[\Override] public function getSubject(): Contract\Subject
    {
        return $this->subject;
    }

    /** @inheritDoc */
    #[\Override] public function getSourceThrowable(): ?\Throwable
    {
        return $this->sourceThrowable;
    }

    /** @inheritDoc */
    #[\Override] public function getSourceErrors(): array
    {
        return $this->sourceErrors;
    }

    /** @inheritDoc */
    #[\Override] public function hasSourceErrors(): bool
    {
        return !empty($this->sourceErrors);
    }

    /** @inheritDoc */
    #[\Override] public function hasSourceThrowable(): bool
    {
        return isset($this->sourceThrowable);
    }

    /** @inheritDoc */
    #[\Override] public function getPathAsString(bool $includeUtility = false): string
    {
        return $this->subject->getPathAsString($includeUtility);
    }
}
