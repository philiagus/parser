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
use Philiagus\Parser\Util\Stringify;

/**
 * Represents the result of a parsing process. Instances might contain errors _or_ the result value, never both.
 *
 * Most important methods of this object are `isSuccess` and `getValue`.
 * If you are using the parsers in `throw mode` the result object can never contain an error.
 *
 * Technically any result object is also a subject that can be used against another parser.
 *
 * @package Subject
 * @see self::getValue()
 * @see self::isSuccess()
 */
readonly class Result extends Subject
{

    /**
     * @param Subject $subject The subject that was used to create this result
     * @param mixed $resultValue The result value - basically the subjects value after transformation
     * @param Error[] $errors A list of errors that has occurred during parsing of the subject
     *                        if any. If errors occurred the Result object will prevent access to the
     *                        result value, given that its content is not to be used
     */
    public function __construct(
        Subject       $subject,
        mixed         $resultValue,
        private array $errors
    )
    {
        parent::__construct($subject, '', $resultValue, true, null);
        foreach ($this->errors as $error) {
            if (!$error instanceof Error) {
                throw new \LogicException(
                    "Trying to create error result with a non ResultError instance: " . Stringify::getType($error)
                );
            }
        }
    }

    /**
     * Returns true if the parsing did not result in any errors
     * If false please use getErrors to get the list of errors that cause the lack of
     * success of this parser
     *
     * @return bool
     * @see hasErrors()
     */
    public function isSuccess(): bool
    {
        return empty($this->errors);
    }

    /**
     * Returns true if this result has been loaded with errors
     * If true it implies that this result was no success and thus
     * isSuccess will return false
     *
     * @return bool
     * @see isSuccess()
     */
    public function hasErrors(): bool
    {
        return (bool)$this->errors;
    }

    /**
     * Returns the result value of this result
     * This method has to throw a \LogicException if the Result is not a success
     *
     * @throws \LogicException
     */
    #[\Override] public function getValue(): mixed
    {
        return empty($this->errors)
            ? parent::getValue()
            : throw new \LogicException(
                "Trying to get result value of failed parsing: " . $this->getSource()->getPathAsString(true)
            );
    }

    /**
     * Returns the list of errors that are embedded in this result
     *
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return $isLastInChain ? '' : ' ↣';
    }
}

