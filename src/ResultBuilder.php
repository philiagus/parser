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

use Philiagus\Parser\Base;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Subject;

class ResultBuilder
{

    private readonly Base\Subject $subject;

    private Base\Subject $currentSubject;

    private mixed $currentValue;

    /** @var Error[] */
    private array $errors = [];

    public function __construct(Base\Subject $subject, string $parserDescription)
    {
        $this->subject = new Subject\ParserBegin($subject, $parserDescription);
        $this->currentSubject = $this->subject;
        $this->currentValue = $this->subject->getValue();
    }

    public function getValue(): mixed
    {
        return $this->currentValue;
    }

    public function incorporateChildResult(Result $result, mixed $defaultValue = null): mixed
    {
        if ($result->isSuccess()) return $result->getValue();

        $errors = $result->getErrors();
        if ($this->subject->throwOnError) {
            $errors[0]->throw();
        }
        $this->errors = [...$this->errors, ...$errors];

        return $defaultValue;
    }

    /**
     * @param string $message
     * @param array $replacers
     * @param \Throwable|null $sourceThrowable
     * @param array $sourceErrors
     *
     * @return void
     * @throws ParsingException
     */
    public function logErrorUsingDebug(
        string      $message,
        array       $replacers = [],
        ?\Throwable $sourceThrowable = null,
        array       $sourceErrors = []
    ): void
    {
        $this->logError(
            Error::createUsingDebugString(
                $this->currentSubject,
                $message,
                $replacers,
                $sourceThrowable,
                $sourceErrors
            )
        );
    }

    /**
     * @param Contract\Error $error
     *
     * @throws ParsingException
     */
    public function logError(Contract\Error $error): void
    {
        if ($this->subject->throwOnError) {
            $error->throw();
        }

        $this->errors[] = $error;
    }

    public function createResult(mixed $newValue): Result
    {
        return new Result($this->currentSubject, $newValue, $this->errors);
    }

    public function createResultUnchanged(): Result
    {
        return new Result($this->currentSubject, $this->subject->getValue(), $this->errors);
    }

    public function createResultFromResult(Result $result): Result
    {
        return new Result(
            $result->sourceSubject,
            $result->isSuccess() ? $result->getValue() : null,
            [...$this->errors, ...$result->getErrors()]
        );
    }

    public function createResultWithCurrentValue(): Result
    {
        return new Result($this->currentSubject, $this->currentValue, $this->errors);
    }

    public function setValue(string $description, mixed $value): void
    {
        $this->currentSubject = new Subject\Internal($this->currentSubject, $description, $value);
        $this->currentValue = $value;
    }

    /**
     * @return Base\Subject
     */
    public function getSubject(): Base\Subject
    {
        return $this->currentSubject;
    }

}
