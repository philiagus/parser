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

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;

/**
 * Parser that always fails, generating an error with a defined message
 *
 * This parser is most times used in conjunction with other Logic parsers, such as Map
 * a certain value to an automatic fail
 *
 * @package Parser\Logic
 */
final readonly class Fail implements Parser
{

    /**
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     */
    protected function __construct(private string $message)
    {
    }

    /**
     * Static constructor to shorthand setting a specific message
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return self
     */
    public static function message(string $message = 'This value can never match'): self
    {
        return new self($message);
    }

    /** @inheritDoc */
    #[\Override] public function parse(Subject $subject): Result
    {
        $error = Error::createUsingStringify($subject, $this->message);
        if ($subject->throwOnError()) {
            throw new ParsingException($error);
        }

        return new Result($subject, null, [$error]);
    }
}
