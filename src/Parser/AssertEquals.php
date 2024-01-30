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
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

class AssertEquals extends Base\Parser
{

    private const string DEFAULT_MESSAGE = 'The value is not equal to the expected value';

    /**
     * AssertEquals constructor.
     *
     * @param mixed $value
     * @param string $exceptionMessage
     */
    private function __construct(
        private readonly mixed  $value,
        private readonly string $exceptionMessage = self::DEFAULT_MESSAGE
    )
    {
    }

    /**
     * Shortcut constructor for assertion against a value when no by-reference check is needed
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - expected: The value the received value is compared against
     *
     * @param $value
     * @param string $exceptionMessage
     *
     * @return static
     *
     * @see Debug::parseMessage()
     *
     */
    public static function value($value, string $exceptionMessage = self::DEFAULT_MESSAGE): static
    {
        return new static($value, $exceptionMessage);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        if ($builder->getValue() != $this->value) {
            $builder->logErrorUsingDebug(
                $this->exceptionMessage,
                ['expected' => $this->value]
            );
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert equals';
    }
}
