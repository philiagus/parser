<?php
/*
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
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\ChainableParser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

/**
 * Parses the provided string treating it as form encoded data
 * The result of the parser is the parsed data
 *
 * @see parse_str()
 */
class ParseFormEncodedString implements ChainableParser
{
    use Chainable;

    /** @var string */
    private string $typeExceptionMessage = 'Provided value is not of type string';

    private function __construct()
    {
    }

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
     * @inheritDoc
     */
    public function parse($value, ?Path $path = null)
    {
        if (!is_string($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        parse_str($value, $result);

        return $result;
    }
}
