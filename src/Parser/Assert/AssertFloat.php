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
use Philiagus\Parser\Exception;
use Philiagus\Parser\Util\Stringify;

/**
 * Asserts the value to be a float. This explicitly excludes NAN, INF and -INF
 * You can define further assertions on the float value (such as min and max)
 *
 * @package Parser\Assert
 * @target-type float
 */
class AssertFloat extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private const string MIN_ERROR_MESSAGE = 'Provided value of {value} is lower than the defined minimum of {min}',
        MAX_ERROR_MESSAGE = 'Provided value of {value} is greater than the defined maximum of {max}',
        RANGE_ERROR_MESSAGE = 'Provided value {value.debug} is not between {min} and {max}';

    /** @var callable[] */
    private array $assertionList = [];

    private function __construct()
    {
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: the set minimum
     *
     * @param float $minimum
     * @param string $errorMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Stringify::parseMessage()
     * @see self::assertMinimum()
     */
    public static function minimum(float $minimum, string $errorMessage = self::MIN_ERROR_MESSAGE): static
    {
        return static::new()->assertMinimum($minimum, $errorMessage);
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: the set minimum
     *
     * @param float $minimum
     * @param string $errorMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Stringify::parseMessage()
     * @see self::minimum()
     */
    public function assertMinimum(float $minimum, string $errorMessage = self::MIN_ERROR_MESSAGE): static
    {
        if (is_nan($minimum) || is_infinite($minimum)) {
            throw new Exception\ParserConfigurationException('Minimum must be set as a float number value. NAN and INF are not allowed');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, float $value) use ($minimum, $errorMessage): void {
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
     * @return static
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * Asserts that the value is <= the provided maximum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - max: the set maximum
     *
     * @param float $maximum
     * @param string $errorMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Stringify::parseMessage()
     * @see self::assertMaximum()
     */
    public static function maximum(float $maximum, string $errorMessage = self::MAX_ERROR_MESSAGE): static
    {
        return static::new()->assertMaximum($maximum, $errorMessage);
    }

    /**
     * Asserts that the value is <= the provided maximum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - max: the set maximum
     *
     * @param float $maximum
     * @param string $errorMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Stringify::parseMessage()
     * @see self::maximum()
     */
    public function assertMaximum(float $maximum, string $errorMessage = self::MAX_ERROR_MESSAGE): static
    {
        if (is_nan($maximum) || is_infinite($maximum)) {
            throw new Exception\ParserConfigurationException('Maximum must be set as a float number value. NAN and INF are not allowed');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, float $value) use ($maximum, $errorMessage): void {
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
     * - min: the set minimum
     * - max: the set maximum
     *
     * @param float $minimum
     * @param float $maximum
     * @param string $errorMessage
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::assertRange()
     */
    public static function range(float $minimum, float $maximum, string $errorMessage = self::RANGE_ERROR_MESSAGE): static
    {
        return static::new()->assertRange($minimum, $maximum, $errorMessage);
    }

    /**
     * Asserts that the value is <= the provided maximum and >= the provided minimum
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: the set minimum
     * - max: the set maximum
     *
     * @param float $minimum
     * @param float $maximum
     * @param string $errorMessage
     * @return $this
     * @see Stringify::parseMessage()
     * @see self::range()
     */
    public function assertRange(float $minimum, float $maximum, string $errorMessage = self::RANGE_ERROR_MESSAGE): static
    {
        if (is_nan($minimum) || is_infinite($minimum) || is_nan($maximum) || is_infinite($maximum)) {
            throw new Exception\ParserConfigurationException('Maximum and minimum must be set as a float number value. NAN and INF are not allowed');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, float $value) use ($minimum, $maximum, $errorMessage): void {
            if ($value < $minimum || $maximum < $value) {
                $builder->logErrorStringify(
                    $errorMessage,
                    [
                        'min' => $minimum,
                        'max' => $maximum,
                    ]
                );
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_float($value) || is_nan($value) || is_infinite($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($builder, $value);
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type float';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert float';
    }
}
