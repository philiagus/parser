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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;

/**
 * Can be used to assert that the value is the same (===) as a provided value or is always the same value
 * throughout runs
 *
 * @package Parser\Assert
 */
class AssertSame extends Base\Parser
{

    protected function __construct(
        private readonly mixed  $value,
        private readonly bool   $compareToMemory,
        private readonly string $errorMessage
    )
    {
    }

    /**
     * Assert provided value is same (===) to a defined value
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The value the received value is compared against
     *
     * @param mixed $value
     * @param string $errorMessage
     *
     * @return static
     *
     * @see Stringify::parseMessage()
     *
     */
    public static function value(mixed $value, string $errorMessage = 'The value is not the same as the expected value'): static
    {
        return new static($value, false, $errorMessage);
    }

    /**
     * Assert provided value is same (===) every time, using the first provided value
     * as defined target
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The value the received value is compared against
     *
     * @param string $errorMessage
     *
     * @return static
     *
     * @see Stringify::parseMessage()
     *
     */
    public static function asFirstValue(string $errorMessage = 'The value is not the same everytime'): static
    {
        return new static(null, true, $errorMessage);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Result
    {
        if ($this->compareToMemory) {
            if (!$builder->hasMemory($this)) {
                $builder->setMemory($this, $builder->getValue());
                return $builder->createResultUnchanged();
            }

            $compareAgainst = $builder->getMemory($this);
        } else {
            $compareAgainst = $this->value;
        }

        if ($builder->getValue() !== $compareAgainst) {
            $builder->logErrorStringify(
                $this->errorMessage,
                ['expected' => $this->value]
            );
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'assert same';
    }
}
