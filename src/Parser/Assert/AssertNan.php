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
 * Asserts that the provided value is NAN
 *
 * @package Parser\Assert
 * @target-type NAN
 */
class AssertNan extends Base\Parser
{

    /** @var string */
    private string $errorMessage;

    private function __construct(string $message)
    {
        $this->errorMessage = $message;
    }

    /**
     * Sets the exception message to be thrown when the provided value is not NAN
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $notNanExceptionMessage
     *
     * @return static
     * @see Stringify::parseMessage()
     *
     */
    public static function new(string $notNanExceptionMessage = 'Provided value is not NAN'): static
    {
        return new static($notNanExceptionMessage);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_float($value) || !is_nan($value)) {
            $builder->logErrorStringify($this->errorMessage);
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert NaN';
    }
}
