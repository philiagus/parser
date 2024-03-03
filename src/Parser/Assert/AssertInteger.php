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
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Util\Stringify;

/**
 * Asserts that the provided value is an integer and also allows to assert for further
 * data in the nature of the integer (such as min/max)
 *
 * @package Parser\Assert
 * @target-type int
 */
class AssertInteger extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var \SplDoublyLinkedList */
    private \SplDoublyLinkedList $assertionList;

    private function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - min: The defined minimum value
     *
     * @param int $minimum
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     *
     */
    public function assertMinimum(int $minimum, string $errorMessage = 'Provided value {subject.debug} is lower than the defined minimum of {min}'): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, int $value) use ($minimum, $errorMessage): void {
            if ($minimum > $value) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['min' => $minimum]
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is <= the provided maximum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - max: The maximum value
     *
     * @param int $maximum
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     *
     */
    public function assertMaximum(int $maximum, string $errorMessage = 'Provided value {subject.debug} is greater than the defined maximum of {max}}'): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, int $value) use ($maximum, $errorMessage): void {
            if ($maximum < $value) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['max' => $maximum]
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is a multiple of the base
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - base: The base set by this call
     *
     * @param int $base
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     *
     */
    public function assertMultipleOf(
        int    $base,
        string $errorMessage = 'Provided value {subject.debug} is not a multiple of {base}'
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, int $value) use ($base, $errorMessage): void {
            if ($value === 0 && $base === 0) return;
            if ($base === 0 || ($value % $base) !== 0) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['base' => $base]
                );
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_int($value)) {
            $this->logTypeError($builder);
        } else {
            foreach ($this->assertionList as $assertion) {
                $assertion($builder, $value);
            }
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type integer';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert integer';
    }
}
