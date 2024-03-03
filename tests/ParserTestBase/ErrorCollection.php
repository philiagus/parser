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

use Philiagus\Parser\Contract;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;

class ErrorCollection
{

    private array $errors = [];
    private bool $configException = false;

    public function add(string $compareType, string $message): self
    {
        $this->errors[] = [$compareType, $message];

        return $this;
    }

    public function isConfigExceptionExpected(): bool
    {
        return $this->configException;
    }

    public function assertException(\Throwable $e): array
    {
        $errors = [];
        $message = $e->getMessage();
        if (!$e instanceof ParsingException) {
            $errors[] = 'Exception is not a ParsingException, it is ' . get_class($e);
        }
        $foundError = false;
        foreach($this->errors as [$compareType, $string]) {
            if($compareType === '=') {
                $foundError = $message === $string;
            } elseif($compareType === 'regex') {
                $foundError = (bool)preg_match($string, $message);
            }
            if($foundError) break;
        }
        if (!$foundError) {
            if ($this->errors) {
                $messageSubpart = ", but expected:";
                foreach($this->errors as [$compareType, $string])
                    $messageSubpart .= PHP_EOL . "\t\t$compareType: " . $string;
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
        $unfoundErrors = [];
        $searchErrors = $this->errors;
        foreach($errors as $error) {
            $found = false;
            foreach($searchErrors as $index => [$compareType, $string]) {
                if($compareType === '=') {
                    $found = $string === $error;
                } else if($compareType === 'regex') {
                    $found = (bool)preg_match($string, $error);
                }
                if($found) {
                    unset($searchErrors[$index]);
                    break;
                }
            }
            if(!$found) {
                $unfoundErrors[] = $error;
            }
        }
        $messages = [];
        if($unfoundErrors) {
            $message = 'Error messages created but not expected:';
            foreach($unfoundErrors as $unfoundError)
                $message .= "\n\t\t- " . $unfoundError;
            $messages[] = $message;
        }
        if($searchErrors) {
            $message = 'Error messages expected but not received:';
            foreach($searchErrors as [$compareType, $string])
                $message .= "\n\t\t- $compareType:" . $string;
            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return $this
     */
    public function expectConfigException(): self
    {
        $this->configException = true;

        return $this;
    }

}
