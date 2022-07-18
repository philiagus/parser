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

    /** @var Error[] */
    private array $errors = [];

    public function __construct(
        Base\Subject $subject,
        string       $parserDescription
    )
    {
        $this->subject = new Subject\Parser($subject, $parserDescription);
        $this->currentSubject = $this->subject;
    }


    /**
     * Used when handing over the value of a property to another parser
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param bool $isPathInValue
     *
     * @return Subject\PropertyValue
     */
    public function subjectPropertyValue(string $propertyName, mixed $propertyValue, bool $isPathInValue = true): Subject\PropertyValue
    {
        return new Subject\PropertyValue($propertyValue, $propertyName, $this->currentSubject, $isPathInValue, $this->currentSubject->throwOnError());
    }

    /**
     * Used when handing over meta information of a value such as the length of a string to another parser
     *
     * @param string $description
     * @param mixed $value
     * @param bool $isPathInValue
     *
     * @return Subject\MetaInformation
     */
    public function subjectMeta(string $description, mixed $value, bool $isPathInValue = true): Subject\MetaInformation
    {
        return new Subject\MetaInformation($value, $description, $this->currentSubject, $isPathInValue, $this->currentSubject->throwOnError());
    }

    public function subjectForwarded(string $description, ?bool $throwOnError = null): Subject\Forwarded
    {
        return new Subject\Forwarded($this->currentSubject, $description, $throwOnError);
    }

    /**
     * Used when handing over the value of a key of an array to another parser
     *
     * @param int|string $index
     * @param mixed $value
     * @param bool $isPathInValue
     *
     * @return Subject\ArrayValue
     */
    public function subjectArrayValue(int|string $index, mixed $value, bool $isPathInValue = true): Subject\ArrayValue
    {
        return new Subject\ArrayValue($value, (string) $index, $this->currentSubject, $isPathInValue, $this->currentSubject->throwOnError());
    }

    /**
     * Used when handing over the key of an array to another parser
     *
     * @param int|string $key
     * @param bool $isPathInValue
     *
     * @return Subject\ArrayKey
     */
    public function subjectArrayKey(int|string $key, bool $isPathInValue = true): Subject\ArrayKey
    {
        return new Subject\ArrayKey($key, (string) $key, $this->currentSubject, $isPathInValue, $this->currentSubject->throwOnError());
    }

    /**
     * Used when handing over the name of a property to another parser
     *
     * @param string $propertyName
     * @param bool $isPathInValue
     *
     * @return Subject\PropertyName
     */
    public function subjectPropertyName(string $propertyName, bool $isPathInValue = true): Subject\PropertyName
    {
        return new Subject\PropertyName($propertyName, $propertyName, $this->currentSubject, $isPathInValue, $this->currentSubject->throwOnError());
    }

    public function subjectInternal(string $description, mixed $value, ?bool $throwOnError = null): Subject\Internal
    {
        return new Subject\Internal($value, $description, $this->currentSubject, false, $throwOnError ?? $this->currentSubject->throwOnError());
    }

    public function incorporateResult(Result $result, mixed $defaultValue = null): mixed
    {
        if ($result->isSuccess()) {
            return $result->getValue();
        }

        $errors = $result->getErrors();
        if ($this->subject->throwOnError()) {
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
                $this->subject,
                $message,
                $replacers,
                $sourceThrowable,
                ...$sourceErrors
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
        if ($this->subject->throwOnError()) {
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
            $result->getSubject(),
            $result->getValue(),
            [...$this->errors, ...$result->getErrors()]
        );
    }

    public function getCurrentSubject(): Base\Subject
    {
        return $this->currentSubject;
    }

    public function setCurrentSubject(Base\Subject $subject): self
    {
        $this->currentSubject = $subject;

        return $this;
    }

    public function createResultBasedOnCurrentSubject(): Result
    {
        return new Result($this->currentSubject, $this->currentSubject->getValue(), $this->errors);
    }

    public function getCurrentValue(): mixed
    {
        return $this->currentSubject->getValue();
    }

}
