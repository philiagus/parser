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

class AssertNan implements Parser
{
    use Chainable, OverridableChainDescription;

    /** @var string */
    private string $exceptionMessage;

    private function __construct(string $message)
    {
        $this->exceptionMessage = $message;
    }

    /**
     * Sets the exception message to be thrown when the provided value is not NAN
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $notNanExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public static function new(string $notNanExceptionMessage = 'Provided value is not NAN'): self
    {
        return new self($notNanExceptionMessage);
    }

    public function parse($value, ?Path $path = null)
    {
        if (is_float($value) && is_nan($value)) return NAN;

        throw new ParsingException(
            $value,
            Debug::parseMessage($this->exceptionMessage, ['value' => $value]),
            $path
        );
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert NaN', false);
    }
}
