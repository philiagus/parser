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


use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;
use Philiagus\Parser\Contract;

class AssertNull extends Base\Parser
{

    /** @var string */
    private string $exceptionMessage;

    private function __construct(string $message)
    {
        $this->exceptionMessage = $message;
    }

    /**
     * Creates a parser with a defined message if the provided value is not NULL
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
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

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): \Philiagus\Parser\Contract\Result
    {
        if ($builder->getValue() !== null) {
            $builder->logErrorUsingDebug($this->exceptionMessage);
        }

        return $builder->createResultUnchanged();
    }

    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert null';
    }
}
