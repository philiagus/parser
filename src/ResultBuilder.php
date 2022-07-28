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

use Philiagus\Parser\Contract;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Subject;

class ResultBuilder
{

    private readonly Contract\Subject $subject;

    private Contract\Subject $currentSubject;

    private mixed $currentValue;

    /** @var Error[] */
    private array $errors = [];

    /**
     * Creates a new ResultBuilder, which internally holds the currently to be parsed value
     * and the currently targeted subject. This target may change throughout a single parser
     * if the parser alters the value to be parsed.
     *
     * The parser description is injected into the subject chain via the ParserBegin subject,
     * which is used if the subject string is created excluding utility elements
     *
     * @param Contract\Subject $subject
     * @param string $parserDescription
     */
    public function __construct(Contract\Subject $subject, string $parserDescription)
    {
        $this->subject = $subject;
        $this->currentSubject = new Subject\Utility\ParserBegin($this->subject, $parserDescription);
        $this->currentValue = $this->subject->getValue();
    }

    /**
     * Returns the current value to be parsed. This value can be changed using the setValue() method
     *
     * @return mixed
     * @see ResultBuilder::setValue()
     */
    public function getValue(): mixed
    {
        return $this->currentValue;
    }

    /**
     * If the provided result is not successful the errors in the result are added to the list of errors
     * that will be added to the result this builder will generate
     *
     * The function returns the value of the provided result or the $defaultValue, if the
     * provided result was not successful
     *
     * If the result is not successful and the subject this result builder currently hold is configured
     * to throw on error, the first error of the provided result is thrown
     *
     * @param Result $result
     * @param mixed $defaultValue
     *
     * @return mixed
     * @throws ParsingException
     */
    public function incorporateResult(Result $result, mixed $defaultValue = null): mixed
    {
        if ($result->isSuccess()) return $result->getValue();

        $errors = $result->getErrors();
        if ($this->subject->throwOnError()) {
            $errors[0]->throw();
        }
        $this->errors = [...$this->errors, ...$errors];

        return $defaultValue;
    }

    /**
     * Adds an error to the builder that will be forwarded to the created result
     * This method is a shortcut to manually creating an error using Error::createUsingDebugString and then
     * calling logError
     *
     * @param string $message
     * @param array $replacers
     * @param \Throwable|null $sourceThrowable
     * @param array $sourceErrors
     *
     * @return static
     * @throws ParsingException
     * @see ResultBuilder::logError()
     * @see Error::createUsingDebugString()
     *
     */
    public function logErrorUsingDebug(
        string      $message,
        array       $replacers = [],
        ?\Throwable $sourceThrowable = null,
        array       $sourceErrors = []
    ): static
    {
        return $this->logError(
            Error::createUsingDebugString($this->currentSubject, $message, $replacers, $sourceThrowable, $sourceErrors)
        );
    }

    /**
     * Adds an error to the result builder that will be forwarded to the result that is being
     * created by this builder
     *
     * If the current subject of the builder has throwOnError set the error is thrown instead
     *
     * @param Contract\Error $error
     *
     * @return static
     * @throws ParsingException
     */
    public function logError(Contract\Error $error): static
    {
        if ($this->subject->throwOnError()) {
            $error->throw();
        }

        $this->errors[] = $error;

        return $this;
    }

    /**
     * Creates a result with the provided value, adding all the accumulated errors to the result (if any)
     *
     * @param mixed $resultValue
     *
     * @return Result
     * @see ResultBuilder::logError()
     * @see ResultBuilder::logErrorUsingDebug()
     *
     */
    public function createResult(mixed $resultValue): Result
    {
        return new Result($this->currentSubject, $resultValue, $this->errors);
    }

    /**
     * Creates a result whose value is identical to the value the current parser was originally started
     * with, adding all the accumulated errors to the result (if any)
     *
     * @return Result
     * @see ResultBuilder::logError()
     * @see ResultBuilder::logErrorUsingDebug()
     *
     */
    public function createResultUnchanged(): Result
    {
        return new Result($this->currentSubject, $this->subject->getValue(), $this->errors);
    }

    /**
     * Creates a result from the provided result.
     * The value of the created result will be identical to the value of the provided result,
     * and the errors will be concat together
     *
     * @param Result $result
     *
     * @return Result
     * @throws ParsingException
     * @see ResultBuilder::logErrorUsingDebug()
     * @see ResultBuilder::logError()
     */
    public function createResultFromResult(Result $result): Result
    {
        if ($this->subject->throwOnError() && !$result->isSuccess()) {
            $result->getErrors()[0]->throw();
        }

        return new Result(
            $result->getSourceSubject(),
            $result->isSuccess() ? $result->getValue() : null,
            [...$this->errors, ...$result->getErrors()]
        );
    }

    /**
     * Creates a result with the current value of this builder, adding all the accumulated errors to the result (if any)
     * The current value of this builder can be altered using the setValue method, which is useful when the
     * currently parsed value is altered multiple times within the same Parser
     *
     * @return Result
     * @see ResultBuilder::logError()
     * @see ResultBuilder::logErrorUsingDebug()
     * @see ResultBuilder::setValue()
     */
    public function createResultWithCurrentValue(): Result
    {
        return new Result($this->currentSubject, $this->currentValue, $this->errors);
    }

    /**
     * Set the current value that is being parsed and add a description of what changed to the subject chain
     * The subject created for this purpose is the Internal subject, which is set to be a utility subject
     *
     * This alters the value of getSubject() and getValue()
     *
     * @param string $description
     * @param mixed $value
     *
     * @return static
     * @see \Philiagus\Parser\Subject\Utility\Internal
     * @see ResultBuilder::getSubject()
     * @see ResultBuilder::getValue()
     */
    public function setValue(string $description, mixed $value): static
    {
        $this->currentSubject = new Subject\Utility\Internal($this->currentSubject, $description, $value);
        $this->currentValue = $value;

        return $this;
    }

    /**
     * Returns the current subject. This subject may change when setValue() is called
     * This method is most times used when creating a new Subject for a child parser, such as when
     * an array key is handed over to another parser for further parsing
     *
     * @return Contract\Subject
     * @see ResultBuilder::setValue()
     */
    public function getSubject(): Contract\Subject
    {
        return $this->currentSubject;
    }

    /**
     * Returns true if this builder already has built up any errors either by logging or incorporating
     * those errors from child results
     *
     * @return bool
     * @see ResultBuilder::logErrorUsingDebug()
     * @see ResultBuilder::incorporateResult()
     *
     * @see ResultBuilder::logError()
     */
    public function hasErrors(): bool
    {
        return (bool) $this->errors;
    }

}
