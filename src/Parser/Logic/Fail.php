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

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Utility\ParserBegin;

class Fail implements Parser
{
    private string $message;

    /**
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $message
     */
    private function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Static constructor to shorthand setting a specific message
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $message
     *
     * @return static
     */
    public static function message(string $message = 'This value can never match'): self
    {
        return new self($message);
    }

    /**
     * @inheritDoc
     */
    public function parse(Subject $subject): Result
    {
        $subject = new ParserBegin($subject, 'FAIL');

        $error = Error::createUsingDebugString($subject, $this->message);
        if ($subject->throwOnError()) {
            $error->throw();
        }

        return new Result($subject, null, [$error]);
    }
}
