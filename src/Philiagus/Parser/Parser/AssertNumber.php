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
use Philiagus\Parser\Util\Debug;

class AssertNumber extends Parser
{
    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of float or integer';

    private $assertionList = [];

    /**
     * Sets the exception message thrown when the type does not match
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $exceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function setTypeExceptionMessage(string $exceptionMessage): self
    {
        $this->typeExceptionMessage = $exceptionMessage;

        return $this;
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
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
    public function withMinimum($minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): self
    {
        if (
            (!is_int($minimum) && !is_float($minimum)) ||
            (is_float($minimum) &&
                (
                    is_nan($minimum) ||
                    is_infinite($minimum)
                )
            )
        ) {
            throw new Exception\ParserConfigurationException('The minimum for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = function ($value, Path $path) use ($minimum, $exceptionMessage) {
            if ($minimum > $value) {
                throw new Exception\ParsingException(
                    $value,
                    Debug::parseMessage($exceptionMessage, ['value' => $value, 'min' => $minimum]),
                    $path
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is <= the provided maximum
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
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
    public function withMaximum($maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): self
    {
        if (
            (!is_int($maximum) && !is_float($maximum)) ||
            (is_float($maximum) &&
                (
                    is_nan($maximum) ||
                    is_infinite($maximum)
                )
            )
        ) {
            throw new Exception\ParserConfigurationException('The maximum for a numeric value must be provided as integer or float');
        }

        $this->assertionList[] = function ($value, Path $path) use ($maximum, $exceptionMessage) {
            if ($maximum < $value) {
                throw new Exception\ParsingException(
                    $value,
                    Debug::parseMessage($exceptionMessage, ['value' => $value, 'max' => $maximum]),
                    $path
                );
            }
        };

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (
            (!is_float($value) && !is_int($value)) ||
            (
                is_float($value) && (
                    is_nan($value) ||
                    is_infinite($value)
                )
            )
        ) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($value, $path);
        }

        return $value;
    }

}