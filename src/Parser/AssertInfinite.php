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
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertInfinite implements Parser
{
    use Chainable, OverwritableChainDescription;

    /** @var string */
    private string $exceptionMessage;

    private ?bool $assertPositive = null;
    private ?string $assertSignMessage = null;

    private function __construct(string $exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;
    }

    /**
     * Sets the exception message sent when the input value is not an infinite value
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $notInfiniteExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public static function new(string $notInfiniteExceptionMessage = 'Provided value is not INF'): self
    {
        return new self($notInfiniteExceptionMessage);
    }

    /**
     * Sets the parser to assert that the infinite value is positiv, so +INF
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $notPositiveMessage
     *
     * @return $this
     */
    public function setAssertSignToPositive(
        string $notPositiveMessage = 'Provided value is not positive infinity'
    ): self
    {
        $this->assertPositive = true;
        $this->assertSignMessage = $notPositiveMessage;

        return $this;
    }

    /**
     * Sets the parser to assert that the infinite value is negative, so -INF
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $notNegativeMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function setAssertSignToNegative(
        string $notNegativeMessage = 'Provided value is not negative infinity'
    ): self
    {
        $this->assertPositive = false;
        $this->assertSignMessage = $notNegativeMessage;

        return $this;
    }

    public function parse($value, Path $path = null): float
    {
        if (!is_float($value) || !is_infinite($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->exceptionMessage, ['value' => $value]),
                $path
            );
        }
        if ($this->assertPositive !== null) {
            if (($value > 0) !== $this->assertPositive) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($this->assertSignMessage, ['value' => $value]),
                    $path
                );
            }
        }

        return $value;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert infinite', false);
    }
}
