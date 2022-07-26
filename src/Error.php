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

namespace Philiagus\Parser;

use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

final class Error implements Contract\Error
{

    /**
     * @param Contract\Subject $subject
     * @param string $message
     * @param \Throwable|null $sourceThrowable
     * @param array $sourceErrors
     */
    public function __construct(
        private readonly Contract\Subject $subject,
        private readonly string                             $message,
        private readonly ?\Throwable                        $sourceThrowable = null,
        private readonly array                              $sourceErrors = []
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
        string                             $message,
        array                              $replacers = [],
        ?\Throwable                        $sourceThrowable = null,
        array                              $sourceErrors = []
    ): Error
    {
        return new Error(
            $subject,
            Debug::parseMessage($message, $replacers + ['subject' => $subject->getValue()]),
            $sourceThrowable,
            $sourceErrors
        );
    }


    /**
     * The message describing the error
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function throw(): never
    {
        throw new ParsingException($this);
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): Contract\Subject
    {
        return $this->subject;
    }

    /**
     * @inheritDoc
     */
    public function getSourceThrowable(): ?\Throwable
    {
        return $this->sourceThrowable;
    }

    /**
     * @inheritDoc
     */
    public function getSourceErrors(): array
    {
        return $this->sourceErrors;
    }

    /**
     * @inheritDoc
     */
    public function hasSourceErrors(): bool
    {
        return !empty($this->sourceErrors);
    }

    /**
     * @inheritDoc
     */
    public function hasSourceThrowable(): bool
    {
        return isset($this->sourceThrowable);
    }

    /**
     * @inheritDoc
     */
    public function getPathAsString(bool $includeUtility = false): string
    {
        return $this->subject->getPathAsString($includeUtility);
    }
}
