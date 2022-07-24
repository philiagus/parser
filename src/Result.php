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

    private readonly bool $success;

    public function __construct(
        Subject                $subject,
        mixed                  $resultValue,
        private readonly array $errors
    )
    {
        parent::__construct($subject, '', $resultValue, true, null);
        if ($this->errors) {
            $this->success = false;
            foreach ($this->errors as $error) {
                if (!$error instanceof Error) {
                    throw new \LogicException(
                        "Trying to create error result with a non ResultError instance: " . Debug::getType($error)
                    );
                }
            }
        } else {
            $this->success = true;
        }
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Returns the result value of this parsing process
     * If the parser resulted in an error a LogicException is thrown
     */
    public function getValue(): mixed
    {
        return $this->success
            ? parent::getValue()
            : throw new \LogicException(
                "Trying to get result value of not successful path " . $this->sourceSubject->getPathAsString(true)
            );
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function getPathStringPart(): string
    {
        return ' then';
    }
}

