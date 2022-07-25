<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Result;
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

    /**
     * @return self
     */
    public static function new(): self
    {
        return new self();
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
     * @return AssertNumber
     * @throws Exception\ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function assertMinimum(float|int $minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): self
    {
        if (
            is_float($minimum) && (is_nan($minimum) || is_infinite($minimum))
        ) {
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
     * @return AssertNumber
     * @throws Exception\ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function assertMaximum(float|int $maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): self
    {
        if (
            is_float($maximum) && (is_nan($maximum) || is_infinite($maximum))
        ) {
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

    /**
     * @inheritDoc
     */
    public function execute(ResultBuilder $builder): Result
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

    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of float or integer';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'asset number';
    }
}
