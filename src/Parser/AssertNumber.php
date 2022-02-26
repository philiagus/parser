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
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Util\Debug;

class AssertNumber implements Parser
{
    use Chainable, OverwritableChainDescription, TypeExceptionMessage;

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
    public function assertMinimum($minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): self
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

        $this->assertionList[] = function ($value, ?Path $path) use ($minimum, $exceptionMessage) {
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
    public function assertMaximum($maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): self
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

        $this->assertionList[] = function ($value, ?Path $path) use ($maximum, $exceptionMessage) {
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

    public function parse($value, ?Path $path = null)
    {
        if (
            false === (
                is_int($value) || (is_float($value) && !is_nan($value) && !is_infinite($value))
            )
        ) {
            $this->throwTypeException($value, $path);
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($value, $path);
        }

        return $value;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not of float or integer';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert number', false);
    }
}
