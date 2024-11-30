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

namespace Philiagus\Parser\Parser\Assert;


use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Util\Stringify;

/**
 * Asserts the value to be `null`
 *
 * @package Parser\Assert
 * @target-type null
 */
class AssertNull extends Base\Parser
{

    /** @var string */
    private string $errorMessage;

    protected function __construct(string $message)
    {
        $this->errorMessage = $message;
    }

    /**
     * Creates a parser with a defined message if the provided value is not NULL
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $notNullExceptionMessage
     *
     * @return static
     * @see Stringify::parseMessage()
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
            $builder->logErrorStringify($this->errorMessage);
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert null';
    }
}
