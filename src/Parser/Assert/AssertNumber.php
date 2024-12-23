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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Stringify;

/**
 * Asserts the provided value to be a number (integer or float). This can be
 * limited further by using the public methods.
 *
 * @package Parser\Assert
 *
 * @see AssertInteger
 * @see AssertFloat
 * @target-type int|float
 */
class AssertNumber extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private const string RANGE_ERROR_MESSAGE = 'Provided value {value.debug} is not between {min} and {max}',
        MIN_ERROR_MESSAGE = 'Provided value {value.debug} is lower than the defined minimum of {min}',
        MAX_ERROR_MESSAGE = 'Provided value {value.debug} is greater than the defined maximum of {max}',
        GT_ERROR_MESSAGE = 'Provided value {value.debug} is not greater than the defined minimum of {lower}',
        LT_ERROR_MESSAGE = 'Provided value {value.debug} is not lower than the defined maximum of {upper}',
        BETWEEN_ERROR_MESSAGE = 'Provided value {value.debug} is not between {lower} and {upper} (excluding)';

    /** @var \SplDoublyLinkedList<callable> */
    protected \SplDoublyLinkedList $assertionList;

    protected function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }

    /**
     * Asserts that lower < value < upper
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - lower: The defined lower boundary value
     * - upper: The defined upper boundary value
     *
     * @param int|float $lower
     * @param int|float $upper
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::assertBetween()
     */
    public static function between(int|float $lower, int|float $upper, string $errorMessage = self::BETWEEN_ERROR_MESSAGE): static
    {
        return static::new()->assertBetween($lower, $upper, $errorMessage);
    }

    /**
     * Asserts that lower < value < upper
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - lower: The defined lower boundary value
     * - upper: The defined upper boundary value
     *
     * @param int|float $lower
     * @param int|float $upper
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::between()
     */
    public function assertBetween(int|float $lower, int|float $upper, string $errorMessage = self::BETWEEN_ERROR_MESSAGE): static
    {
        if (
            (is_float($upper) && is_nan($upper)) ||
            (is_float($lower) && is_nan($lower))
        ) {
            throw new Exception\ParserConfigurationException('The lower and upper bound for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, int|float $value) use ($lower, $upper, $errorMessage): void {
            if (!($lower < $value && $value < $upper)) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['upper' => $upper, 'lower' => $lower]
                );
            }
        };

        return $this;
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Asserts that the value is < the provided boundary
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - lower: The defined lower boundary value
     *
     * @param int|float $upper
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::assertLowerThan()
     */
    public static function lowerThan(int|float $upper, string $errorMessage = self::LT_ERROR_MESSAGE): static
    {
        return static::new()->assertLowerThan($upper, $errorMessage);
    }

    /**
     * Asserts that the value is < the provided boundary
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - lower: The defined lower boundary value
     *
     * @param int|float $upper
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::lowerThan()
     */
    public function assertLowerThan(int|float $upper, string $errorMessage = self::LT_ERROR_MESSAGE): static
    {
        if (is_float($upper) && is_nan($upper)) {
            throw new Exception\ParserConfigurationException('The upper bound for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, int|float $value) use ($upper, $errorMessage): void {
            if ($value >= $upper) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['upper' => $upper]
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is > the provided boundary
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - lower: The defined lower boundary value
     *
     * @param int|float $lower
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::assertGreaterThan()
     */
    public static function greaterThan(int|float $lower, string $errorMessage = self::GT_ERROR_MESSAGE): static
    {
        return static::new()->assertGreaterThan($lower, $errorMessage);
    }

    /**
     * Asserts that the value is > the provided boundary
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - lower: The defined lower boundary value
     *
     * @param int|float $lower
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::greaterThan()
     */
    public function assertGreaterThan(int|float $lower, string $errorMessage = self::GT_ERROR_MESSAGE): static
    {
        if (is_float($lower) && is_nan($lower)) {
            throw new Exception\ParserConfigurationException('The lower bound for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, int|float $value) use ($lower, $errorMessage): void {
            if ($value <= $lower) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['lower' => $lower]
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: The set minimum value
     *
     * @param float|int $minimum
     * @param string $errorMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Stringify::parseMessage()
     * @see self::assertMinimum()
     */
    public static function minimum(float|int $minimum, string $errorMessage = self::MIN_ERROR_MESSAGE): static
    {
        return static::new()->assertMinimum($minimum, $errorMessage);
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: The set minimum value
     *
     * @param float|int $minimum
     * @param string $errorMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Stringify::parseMessage()
     * @see self::minimum()
     */
    public function assertMinimum(float|int $minimum, string $errorMessage = self::MIN_ERROR_MESSAGE): static
    {
        if (is_float($minimum) && is_nan($minimum)) {
            throw new Exception\ParserConfigurationException('The minimum for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, int|float $value) use ($minimum, $errorMessage): void {
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
     * - value: The value currently being parsed
     * - max: The currently set maximum
     *
     * @param float|int $maximum
     * @param string $errorMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Stringify::parseMessage()
     * @see self::assertMaximum()
     */
    public static function maximum(float|int $maximum, string $errorMessage = self::MAX_ERROR_MESSAGE): static
    {
        return static::new()->assertMaximum($maximum, $errorMessage);
    }

    /**
     * Asserts that the value is <= the provided maximum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - max: The currently set maximum
     *
     * @param float|int $maximum
     * @param string $errorMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Stringify::parseMessage()
     * @see self::maximum()
     */
    public function assertMaximum(float|int $maximum, string $errorMessage = self::MAX_ERROR_MESSAGE): static
    {
        if (is_float($maximum) && is_nan($maximum)) {
            throw new Exception\ParserConfigurationException('The maximum for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, int|float $value) use ($maximum, $errorMessage): void {
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
     * - value: The value currently being parsed
     * - min: The currently set minimum
     * - max: The currently set maximum
     *
     * @param float|int $minimum
     * @param float|int $maximum
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::assertRange()
     */
    public static function range(float|int $minimum, float|int $maximum, string $errorMessage = self::RANGE_ERROR_MESSAGE): static
    {
        return static::new()->assertRange($minimum, $maximum, $errorMessage);
    }

    /**
     * Asserts that the value is <= the provided maximum and >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: The currently set minimum
     * - max: The currently set maximum
     *
     * @param float|int $minimum
     * @param float|int $maximum
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::range()
     */
    public function assertRange(float|int $minimum, float|int $maximum, string $errorMessage = self::RANGE_ERROR_MESSAGE): static
    {
        if (
            (is_float($maximum) && is_nan($maximum)) ||
            (is_float($minimum) && is_nan($minimum))
        ) {
            throw new Exception\ParserConfigurationException('The minimum and maximum for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, int|float $value) use ($minimum, $maximum, $errorMessage): void {
            if ($value < $minimum || $maximum < $value) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['min' => $minimum, 'max' => $maximum]
                );
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        if ($this->isSupportedType($value)) {
            foreach ($this->assertionList as $assertion) {
                $assertion($builder, $value);
            }
        } else {
            $this->logTypeError($builder);
        }

        return $builder->createResultUnchanged();
    }

    /**
     * Returns true if the type matches
     * @param mixed $value
     * @return bool
     */
    protected function isSupportedType(mixed $value): bool
    {
        return is_int($value) || (is_float($value) && !is_nan($value) && !is_infinite($value));
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of float or integer';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'assert number';
    }
}
