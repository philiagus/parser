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

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class Error implements Contract\Error
{

    public function __construct(
        private readonly Subject     $subject,
        private readonly string      $message,
        private readonly ?\Throwable $sourceThrowable = null,
        private readonly array       $sourceErrors = []
    )
    {
        foreach ($sourceErrors as $sourceError) {
            if (!$sourceError instanceof Error) {
                throw new \LogicException(
                    "Error class has been filled wi non-Error instance as sourceError: " . Debug::stringify($sourceError)
                );
            }
        }
    }

    public static function createUsingDebugString(
        Subject     $subject,
        string      $message,
        array       $replacers = [],
        ?\Throwable $sourceThrowable = null,
        array       $sourceErrors = []
    ): self
    {
        return new self(
            $subject,
            Debug::parseMessage($message, $replacers + ['value' => $subject->getValue()]),
            $sourceThrowable,
            $sourceErrors
        );
    }


    public function getMessage(): string
    {
        return $this->message;
    }

    public function throw(): never
    {
        throw new ParsingException($this);
    }

    /**
     * @return Subject
     */
    public function getSubject(): Subject
    {
        return $this->subject;
    }

    /**
     * @return \Throwable|null
     */
    public function getSourceThrowable(): ?\Throwable
    {
        return $this->sourceThrowable;
    }

    /**
     * @return Error[]
     */
    public function getSourceErrors(): array
    {
        return $this->sourceErrors;
    }

    /**
     * @return bool
     */
    public function hasSourceErrors(): bool
    {
        return !empty($this->sourceErrors);
    }
}
