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
 * Asserts that the provided value is INF or -INF. You can limit it to either by
 * using the corresponding setters.
 *
 * @package Parser\Assert
 * @target-type INF|-INF
 */
class AssertInfinite extends Base\Parser
{

    private string $errorMessage;
    private ?bool $assertPositive = null;
    private ?string $assertSignMessage = null;

    private function __construct(string $exceptionMessage)
    {
        $this->errorMessage = $exceptionMessage;
    }

    /**
     * Creates a parser that asserts that the value is INF or -INF (which can be refined using methods)
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $notInfiniteExceptionMessage
     *
     * @return static
     * @see Stringify::parseMessage()
     */
    public static function new(string $notInfiniteExceptionMessage = 'Provided value is not INF'): static
    {
        return new static($notInfiniteExceptionMessage);
    }

    /**
     * Sets the parser to assert that the infinite value is positiv, so +INF
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $notPositiveMessage
     *
     * @return $this
     */
    public function setAssertPositive(
        string $notPositiveMessage = 'Provided value is not positive infinity'
    ): static
    {
        $this->assertPositive = true;
        $this->assertSignMessage = $notPositiveMessage;

        return $this;
    }

    /**
     * Sets the parser to assert that the infinite value is negative, so -INF
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $notNegativeMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     */
    public function setAssertNegative(
        string $notNegativeMessage = 'Provided value is not negative infinity'
    ): static
    {
        $this->assertPositive = false;
        $this->assertSignMessage = $notNegativeMessage;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_float($value) || !is_infinite($value)) {
            $builder->logErrorStringify($this->errorMessage);
        }
        if ($this->assertPositive !== null) {
            if (($value > 0) !== $this->assertPositive) {
                $builder->logErrorStringify($this->assertSignMessage);
            }
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert infinite';
    }
}
