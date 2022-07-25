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
use Philiagus\Parser\Contract\Error;
use Philiagus\Parser\Util\Debug;

class Result extends Subject
{

    /**
     * @param Subject $subject
     * @param mixed $resultValue
     * @param array $errors
     */
    public function __construct(
        Subject                $subject,
        mixed                  $resultValue,
        private readonly array $errors
    )
    {
        parent::__construct($subject, '', $resultValue, true, null);
        foreach ($this->errors as $error) {
            if (!$error instanceof Error) {
                throw new \LogicException(
                    "Trying to create error result with a non ResultError instance: " . Debug::getType($error)
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
     * @see Result::isSuccess()
     */
    public function hasErrors(): bool
    {
        return (bool) $this->errors;
    }

    /**
     * Returns the result value of this parsing process
     * If the parser resulted in an error a LogicException is thrown
     */
    public function getValue(): mixed
    {
        return empty($this->errors)
            ? parent::getValue()
            : throw new \LogicException(
                "Trying to get result value of not successful path " . $this->getSourceSubject()->getPathAsString(true)
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

    /**
     * @inheritDoc
     */
    protected function getPathStringPart(bool $isLastInChain): string
    {
        return $isLastInChain ? '' : ' ↣';
    }
}

