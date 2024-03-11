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

    private const string RANGE_ERROR_MESSAGE = 'Provided value {value.debug} is not between {min} and {max}',
        MIN_ERROR_MESSAGE = 'Provided value {value.debug} is lower than the defined minimum of {min}',
        MAX_ERROR_MESSAGE = 'Provided value {value.debug} is greater than the defined maximum of {max}}';

    /** @var \SplDoublyLinkedList */
    private \SplDoublyLinkedList $assertionList;

    private function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }


    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: The defined minimum value
     *
     * @param int $minimum
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::assertMinimum()
     */
    public static function minimum(int $minimum, string $errorMessage = self::MIN_ERROR_MESSAGE): static
    {
        return self::new()->assertMinimum($minimum, $errorMessage);
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: The defined minimum value
     *
     * @param int $minimum
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::minimum()
     */
    public function assertMinimum(int $minimum, string $errorMessage = self::MIN_ERROR_MESSAGE): static
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
     * Creates a new instance of the parser
     *
     * @return static
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * Creates the parser asserting that the value ist <= the provided maximum
     *
     *  The message is processed using Stringify::parseMessage and receives the following elements:
     *  - value: The value currently being parsed
     *  - max: The maximum value
     *
     * @param int $maximum
     * @param string $errorMessage
     *
     * @return static
     * @see Stringify::parseMessage()
     * @see self::assertMaximum()
     */
    public static function maximum(int $maximum, string $errorMessage = self::MAX_ERROR_MESSAGE): static
    {
        return self::new()->assertMaximum($maximum, $errorMessage);
    }

    /**
     * Asserts that the value is <= the provided maximum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - max: The maximum value
     *
     * @param int $maximum
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::maximum()
     */
    public function assertMaximum(int $maximum, string $errorMessage = self::MAX_ERROR_MESSAGE): static
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
     * Asserts that the value is <= the provided maximum and >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     *  - value: The value currently being parsed
     *  - max: The maximum value
     *  - min: The minimum value
     * @param int $minimum
     * @param int $maximum
     * @param string $errorMessage
     * @return $this
     *
     * @see Stringify::parseMessage()
     * @see self::assertRange()
     */
    public static function range(
        int    $minimum,
        int    $maximum,
        string $errorMessage = self::RANGE_ERROR_MESSAGE
    ): static
    {
        return self::new()->assertRange($minimum, $maximum, $errorMessage);
    }

    /**
     * Asserts that the value is <= the provided maximum and >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     *  - value: The value currently being parsed
     *  - max: The maximum value
     *  - min: The minimum value
     * @param int $minimum
     * @param int $maximum
     * @param string $errorMessage
     * @return $this
     *
     * @see Stringify::parseMessage()
     * @see self::range()
     */
    public function assertRange(
        int    $minimum,
        int    $maximum,
        string $errorMessage = self::RANGE_ERROR_MESSAGE
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, int $value) use ($minimum, $maximum, $errorMessage): void {
            if ($minimum > $value || $maximum < $value) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['min' => $minimum, 'max' => $maximum]
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is a multiple of the base
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
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
        string $errorMessage = 'Provided value {value.debug} is not a multiple of {base}'
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
