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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertEquals implements Parser
{
    use Chainable, OverridableChainDescription;

    private const DEFAULT_MESSAGE = 'The value is not equal to the expected value';

    /** @var string */
    private string $exceptionMessage;

    /** @var mixed */
    private $targetValue;

    /**
     * AssertEquals constructor.
     *
     * @param $value
     * @param string $exceptionMessage
     */
    private function __construct($value, string $exceptionMessage = self::DEFAULT_MESSAGE)
    {
        $this->targetValue = $value;
        $this->exceptionMessage = $exceptionMessage;
    }

    /**
     * Shortcut constructor for assertion against a value when no by-reference check is needed
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The value the received value is compared against
     *
     * @param $value
     * @param string $exceptionMessage
     *
     * @return static
     *
     * @see Debug::parseMessage()
     *
     */
    public static function value($value, string $exceptionMessage = self::DEFAULT_MESSAGE): self
    {
        return new self($value, $exceptionMessage);
    }

    public function parse($value, Path $path = null)
    {
        if ($value == $this->targetValue) return $value;

        throw new ParsingException(
            $value,
            Debug::parseMessage(
                $this->exceptionMessage,
                [
                    'value' => $value,
                    'expected' => $this->targetValue,
                ]
            ),
            $path
        );
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert equals', false);
    }
}
