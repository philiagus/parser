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
use Philiagus\Parser\Subject\Chain;
use Philiagus\Parser\Util\Debug;

class Result
{

    private readonly bool $success;

    public function __construct(
        private readonly Subject $subject,
        private readonly mixed   $resultValue,
        private readonly array   $errors
    )
    {
        $this->success = empty($this->errors);
        foreach ($this->errors as $error) {
            if (!$error instanceof Error) {
                throw new \LogicException(
                    "Trying to create error result with a non ResultError instance: " . Debug::getType($error)
                );
            }
        }
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getValue(): mixed
    {
        if ($this->success) return $this->resultValue;

        throw new \LogicException(
            "Trying to get result value of not successful result of path " . $this->subject->getPathAsString()
        );
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function subjectChain(): Chain
    {
        return new Chain($this->getSubject(), $this->resultValue);
    }
}

