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

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverridableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertFloat implements Parser
{
    use Chainable, OverridableChainDescription;

    /** @var string */
    private string $typeExceptionMessage = 'Provided value is not of type float';

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
     * Defines the exception message to use if the value is not a string
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function setTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Asserts that the value is >= the provided minimum
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - min: the set minimum
     *
     * @param float $minimum
     * @param string $exceptionMessage
     *
     * @return AssertFloat
     * @throws Exception\ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function assertMinimum(float $minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): self
    {
        if (is_nan($minimum) || is_infinite($minimum)) {
            throw new Exception\ParserConfigurationException('Minimum must be set as a float number value. NAN and INF are not allowed');
        }

        $this->assertionList[] = function (float $value, ?Path $path) use ($minimum, $exceptionMessage) {
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
     * - max: the set maximum
     *
     * @param float $maximum
     * @param string $exceptionMessage
     *
     * @return AssertFloat
     * @throws Exception\ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function assertMaximum(float $maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): self
    {
        if (is_nan($maximum) || is_infinite($maximum)) {
            throw new Exception\ParserConfigurationException('Maximum must be set as a float number value. NAN and INF are not allowed');
        }

        $this->assertionList[] = function (float $value, ?Path $path) use ($maximum, $exceptionMessage) {
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

    public function parse($value, Path $path = null)
    {
        if (!is_float($value) || is_nan($value) || is_infinite($value)) {
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

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert float');
    }
}
