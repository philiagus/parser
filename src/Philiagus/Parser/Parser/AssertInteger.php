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

class AssertInteger extends Parser
{
    private $typeExceptionMessage = 'Provided value is not of type integer';

    /**
     * @var array|null
     */
    private $minimumValue = null;

    /**
     * @var array|null
     */
    private $maximumValue = null;

    /**
     * @var null|int
     */
    private $divisibleByValue = null;

    public function withTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Asserts that the value is >= the provided minimum
     * Replacers in the exception message:
     * {value} = parsed value
     * {min} = currently set minimum
     *
     * @param int $minimum
     * @param string $exceptionMessage
     *
     * @return AssertInteger
     * @throws Exception\ParserConfigurationException
     */
    public function withMinimum(int $minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): self
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
     * @param int $maximum
     * @param string $exceptionMessage
     *
     * @return AssertInteger
     * @throws Exception\ParserConfigurationException
     */
    public function withMaximum(int $maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): self
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
     * Asserts that the value is a multiple of the base
     * Replacers in the exception message:
     * {value} = parsed value
     * {base} = currently set base
     *
     * @param int $base
     * @param string $exceptionMessage
     *
     * @return AssertInteger
     */
    public function isMultipleOf(
        int $base,
        string $exceptionMessage = 'Provided value of {value} is not a multiple of {base}'
    ): self
    {
        $this->divisibleByValue = [$base, $exceptionMessage];

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_int($value)) {
            throw new Exception\ParsingException($value, $this->typeExceptionMessage, $path);
        }

        if ($this->minimumValue !== null) {
            /**
             * @var int $minimum
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

        if ($this->maximumValue !== null) {
            /**
             * @var int $maximum
             * @var string $exception
             */
            [$maximum, $exception] = $this->maximumValue;
            if ($maximum < $value) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exception, ['{value}' => $value, '{max}' => $maximum]),
                    $path
                );
            }
        }

        if ($this->divisibleByValue !== null) {
            /**
             * @var int $base
             * @var string $exception
             */
            [$base, $exception] = $this->divisibleByValue;
            if (
                ($base === 0 && $value !== 0) ||
                ($base !== 0 && $value % $base !== 0)
            ) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exception, ['{value}' => $value, '{base}' => $base]),
                    $path
                );
            }
        }

        return $value;
    }
}