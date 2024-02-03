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

namespace Philiagus\Parser\Parser;


use Philiagus\Parser\Base;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Parser\Logic\OneOf;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

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
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $notNullExceptionMessage
     *
     * @return static
     * @see Debug::parseMessage()
     *
     */
    public static function new(string $notNullExceptionMessage = 'Provided value is not NULL'): static
    {
        return new static($notNullExceptionMessage);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        if ($builder->getValue() !== null) {
            $builder->logErrorUsingDebug($this->exceptionMessage);
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert null';
    }
}
