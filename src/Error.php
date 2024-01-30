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
use Philiagus\Parser\Util\Debug;

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
                    "Error class has been filled with non-Error instance as sourceError: " . Debug::stringify($sourceError)
                );
            }
        }
    }

    /**
     * Creates the error using Debug::parseMessage with $message and $replacers
     * The subject will by default be provided as a 'subject' replacer target
     * So you can use {subject} as a replacer in all calls to this method
     *
     * @param Contract\Subject $subject
     * @param string $message
     * @param array $replacers
     * @param \Throwable|null $sourceThrowable
     * @param array $sourceErrors
     *
     * @return Error
     * @see Debug::parseMessage()
     *
     */
    public static function createUsingDebugString(
        Contract\Subject $subject,
        string           $message,
        array            $replacers = [],
        ?\Throwable      $sourceThrowable = null,
        array            $sourceErrors = []
    ): Error
    {
        return new Error(
            $subject,
            Debug::parseMessage($message, $replacers + ['subject' => $subject->getValue()]),
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
