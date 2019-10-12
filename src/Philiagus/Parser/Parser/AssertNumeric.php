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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Exception\ParsingException;

class AssertNumeric extends Parser
{
    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of float or integer';

    /**
     * @var array|null
     */
    private $minimumValue = null;

    /**
     * @var array|null
     */
    private $maximumValue = null;

    /**
     * Sets the exception message thrown when the type does not match
     * @param string $exceptionMessage
     *
     * @return $this
     */
    public function withTypeExceptionMessage(string $exceptionMessage): self
    {
        $this->typeExceptionMessage = $exceptionMessage;

        return $this;
    }

    /**
     * Asserts that the value is >= the provided minimum
     * Replacers in the exception message:
     * {value} = parsed value
     * {min} = currently set minimum
     *
     * @param float|int $minimum
     * @param string $exceptionMessage
     *
     * @return AssertNumeric
     * @throws Exception\ParserConfigurationException
     */
    public function withMinimum($minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): self
    {
        if(
            (!is_int($minimum) && !is_float($minimum)) ||
            is_nan($minimum) ||
            is_infinite($minimum)
        ) {
            throw new Exception\ParserConfigurationException('The minimum for a numeric value must be provided as integer or float');
        }
        if ($this->maximumValue !== null && $this->maximumValue[0] < $minimum) {
            throw new Exception\ParserConfigurationException(
                sprintf('Trying to set minimum of %s to a higher value than the maximum of %s', $minimum, $this->maximumValue)
            );
        }

        $this->minimumValue = [$minimum, $exceptionMessage];

        return $this;
    }

    /**
     * Asserts that the value is <= the provided maximum
     * Replacers in the exception message:
     * {value} = parsed value
     * {max} = currently set maximum
     *
     * @param float|int $maximum
     * @param string $exceptionMessage
     *
     * @return AssertNumeric
     * @throws Exception\ParserConfigurationException
     */
    public function withMaximum($maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): self
    {
        if(
            (!is_int($maximum) && !is_float($maximum)) ||
            is_nan($maximum) ||
            is_infinite($maximum)
        ) {
            throw new Exception\ParserConfigurationException('The maximum for a numeric value must be provided as integer or float');
        }

        if ($this->minimumValue !== null && $this->minimumValue[0] > $maximum) {
            throw new Exception\ParserConfigurationException(
                sprintf('Trying to set maximum of %s to a lower value than the minimum of %s', $maximum, $this->minimumValue)
            );
        }

        $this->maximumValue = [$maximum, $exceptionMessage];

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (
            (!is_float($value) && !is_int($value)) ||
            is_nan($value) ||
            is_infinite($value)) {
            throw new ParsingException($value, $this->typeExceptionMessage, $path);
        }

        if ($this->minimumValue !== null) {
            /**
             * @var float|int $minimum
             * @var string $exception
             */
            [$minimum, $exception] = $this->minimumValue;
            if ($minimum > $value) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exception, ['{value}' => $value, '{min}' => $minimum]),
                    $path
                );
            }
        }

        if($this->maximumValue !== null) {
            /**
             * @var float|int $maximum
             * @var string $exception
             */
            [$maximum, $exception] = $this->maximumValue;
            if($maximum < $value) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exception, ['{value}' => $value, '{max}' => $maximum]),
                    $path
                );
            }
        }

        return $value;
    }

}