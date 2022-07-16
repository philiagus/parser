<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Parser;


use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class AssertNull implements Parser
{
    use Chainable, OverwritableParserDescription;

    /** @var string */
    private string $exceptionMessage;

    private function __construct(string $message)
    {
        $this->exceptionMessage = $message;
    }

    /**
     * Creates a parser with a defined message if the provided value is not NULL
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $notNullExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public static function new(string $notNullExceptionMessage = 'Provided value is not NULL'): self
    {
        return new self($notNullExceptionMessage);
    }

    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        if($subject->getValue() !== null) {
            $builder->logErrorUsingDebug($this->exceptionMessage);
        }

        return $builder->createResultUnchanged();
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'assert null';
    }
}
