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

class ParseBase64String implements ChainableParser
{
    use Chainable;

    /** @var string */
    private string $typeExceptionMessage = 'Provided value is not of type string';

    /** @var bool */
    private bool $strict = true;

    /** @var string */
    private string $notBase64ExceptionMessage = 'The provided value is not a valid base64 sequence';

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
     * Defines the exception message to use if the value is not a valid base64 string
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
    public function setNotBase64ExceptionMessage(string $message): self
    {
        $this->notBase64ExceptionMessage = $message;

        return $this;
    }

    /**
     * @param bool $strict
     *
     * @return $this
     */
    public function setStrict(bool $strict = true): self
    {
        $this->strict = $strict;

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

        $result = base64_decode($value, $this->strict);

        if ($result === false) {
            throw new ParsingException(
                $value,
                Debug::parseMessage(
                    $this->notBase64ExceptionMessage,
                    ['value' => $value]
                ),
                $path
            );
        }

        return $result;
    }
}
