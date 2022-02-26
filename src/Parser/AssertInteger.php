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
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Util\Debug;

class AssertInteger implements Parser
{
    use Chainable, OverridableChainDescription, TypeExceptionMessage;

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
     * - min: The defined minimum value
     *
     * @param int $minimum
     * @param string $exceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function assertMinimum(int $minimum, string $exceptionMessage = 'Provided value {value.debug} is lower than the defined minimum of {min}'): self
    {
        $this->assertionList[] = function (int $value, ?Path $path) use ($minimum, $exceptionMessage) {
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
     * - max: The maximum value
     *
     * @param int $maximum
     * @param string $exceptionMessage
     *
     * @return AssertInteger
     * @see Debug::parseMessage()
     *
     */
    public function assertMaximum(int $maximum, string $exceptionMessage = 'Provided value {value.debug} is greater than the defined maximum of {max}}'): self
    {
        $this->assertionList[] = function (int $value, ?Path $path) use ($maximum, $exceptionMessage) {
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
     * Asserts that the value is a multiple of the base
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - base: The base set by this call
     *
     * @param int $base
     * @param string $exceptionMessage
     *
     * @return AssertInteger
     * @see Debug::parseMessage()
     *
     */
    public function assertMultipleOf(
        int    $base,
        string $exceptionMessage = 'Provided value {value.debug} is not a multiple of {base}'
    ): self
    {
        $this->assertionList[] = function (int $value, ?Path $path) use ($base, $exceptionMessage) {
            if ($value === 0 && $base === 0) return;
            if (($value % $base) !== 0) {
                throw new Exception\ParsingException(
                    $value,
                    Debug::parseMessage($exceptionMessage, ['value' => $value, 'base' => $base]),
                    $path
                );
            }
        };

        return $this;
    }

    public function parse($value, Path $path = null)
    {
        if (!is_int($value)) {
            $this->throwTypeException($value, $path);
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($value, $path);
        }

        return $value;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not of type integer';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert integer', false);
    }
}
