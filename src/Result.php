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
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Error;
use Philiagus\Parser\Util\Debug;

class Result extends Subject implements Contract\Result
{

    /**
     * @param Contract\Subject $subject
     * @param mixed $resultValue
     * @param array $errors
     */
    public function __construct(
        Contract\Subject       $subject,
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
     * @inheritDoc
     */
    public function isSuccess(): bool
    {
        return empty($this->errors);
    }

    /**
     * @inheritDoc
     */
    public function hasErrors(): bool
    {
        return (bool) $this->errors;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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

