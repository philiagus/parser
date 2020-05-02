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
     * @var callable[]
     */
    private $assertionList = [];

    /**
     * Sets the exception message thrown when the type does not match
     *
     * @param string $exceptionMessage
     *
     * @return $this
     */
    public function overwriteTypeExceptionMessage(string $exceptionMessage): self
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
        if (is_nan($minimum) || is_infinite($minimum)) {
            throw new Exception\ParserConfigurationException('Minimum must be set as a float number value. NAN and INF are not allowed');
        }

        $this->assertionList[] = function (float $value, Path $path) use ($minimum, $exceptionMessage) {
            if ($minimum > $value) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exceptionMessage, ['{value}' => $value, '{min}' => $minimum]),
                    $path
                );
            }
        };

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
        if (is_nan($maximum) || is_infinite($maximum)) {
            throw new Exception\ParserConfigurationException('Maximum must be set as a float number value. NAN and INF are not allowed');
        }

        $this->assertionList[] = function (float $value, Path $path) use ($maximum, $exceptionMessage) {
            if ($maximum < $value) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exceptionMessage, ['{value}' => $value, '{max}' => $maximum]),
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
        if (!is_float($value) || is_nan($value) || is_infinite($value)) {
            throw new ParsingException($value, $this->typeExceptionMessage, $path);
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($value, $path);
        }

        return $value;
    }
}