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
use Philiagus\Parser\Contract;
use Philiagus\Parser\Exception;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

class AssertNumber extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var callable[] */
    private array $assertionList = [];

    private function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - min: The set minimum value
     *
     * @param float|int $minimum
     * @param string $exceptionMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function assertMinimum(float|int $minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): static
    {
        if (is_float($minimum) && (is_nan($minimum) || is_infinite($minimum))) {
            throw new Exception\ParserConfigurationException('The minimum for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, int|float $value) use ($minimum, $exceptionMessage): void {
            if ($minimum > $value) {
                $builder->logErrorUsingDebug(
                    $exceptionMessage,
                    ['min' => $minimum]
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is <= the provided maximum
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - max: The currently set maximum
     *
     * @param float|int $maximum
     * @param string $exceptionMessage
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function assertMaximum(float|int $maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): static
    {
        if (is_float($maximum) && (is_nan($maximum) || is_infinite($maximum))) {
            throw new Exception\ParserConfigurationException('The maximum for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = static function (ResultBuilder $builder, int|float $value) use ($maximum, $exceptionMessage): void {
            if ($maximum < $value) {
                $builder->logErrorUsingDebug(
                    $exceptionMessage,
                    ['max' => $maximum]
                );
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (
            is_int($value) ||
            (is_float($value) && !is_nan($value) && !is_infinite($value))
        ) {
            foreach ($this->assertionList as $assertion) {
                $assertion($builder, $value);
            }
        } else {
            $this->logTypeError($builder);
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of float or integer';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert number';
    }
}
