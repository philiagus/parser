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

namespace Philiagus\Parser\Test\ParserTestBase;

use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Contract;

class ErrorCollection
{

    private array $errors = [];

    public function add(string $message): self
    {
        $this->errors[] = $message;

        return $this;
    }

    public function assertException(\Throwable $e): array
    {
        $errors = [];
        $message = $e->getMessage();
        if (!$e instanceof ParsingException) {
            $errors[] = 'Exception is not a ParsingException, it is ' . get_class($e);
        }
        if (!in_array($message, $this->errors)) {
            if ($this->errors) {
                $messageSubpart = ", but expected:" . PHP_EOL . "\t\t" . implode(PHP_EOL . "\t\t", $this->errors);
            } else {
                $messageSubpart = ' as none were expected';
            }
            $errors[] = "Exception message '$message' @ {$e->getFile()}:{$e->getLine()} was not expected$messageSubpart";
        }

        return $errors;
    }

    public function assertResult(Contract\Result $result): array
    {
        $errors = array_map(fn(Error $error) => $error->getMessage(), $result->getErrors());
        sort($errors);
        sort($this->errors);
        if ($this->errors !== $errors) {
            $message = "Error messages do not match\n\tExpected:\n\t\t- " .
                implode("\n\t\t- ", $this->errors) . "\n\tReceived:\n\t\t- " .
                implode("\n\t\t- ", $errors);

            return [$message];
        }

        return [];
    }

}
