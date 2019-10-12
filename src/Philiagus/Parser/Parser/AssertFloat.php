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

class AssertFloat extends Parser
{
    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of type float';

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
     * @param float $minimum
     * @param string $exceptionMessage
     *
     * @return AssertFloat
     * @throws Exception\ParserConfigurationException
     */
    public function withMinimum(float $minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): self
    {
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
     * @param float $maximum
     * @param string $exceptionMessage
     *
     * @return AssertFloat
     * @throws Exception\ParserConfigurationException
     */
    public function withMaximum(float $maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): self
    {
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
        if (!is_float($value) || is_nan($value) || is_infinite($value)) {
            throw new ParsingException($value, $this->typeExceptionMessage, $path);
        }

        if ($this->minimumValue !== null) {
            /**
             * @var float $minimum
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
             * @var float $maximum
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