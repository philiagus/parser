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

use DateTimeZone;
use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\ChainableParser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class ConvertToDateTime implements ChainableParser
{
    use Chainable;

    private ?string $sourceFormat = null;
    private ?DateTimeZone $sourceTimezone = null;
    private string $sourceFormatException = '';
    private bool $immutable = false;
    private ?DateTimeZone $timezone = null;
    private string $typeExceptionMessage = 'Provided value could not be converted to DateTime';

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
     * Specifies the source format when receiving a string and trying to convert it to a DateTime/DateTimeImmutable
     * object.
     *
     * The exception message is thrown when the string value does not match the expected format.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - format: The format provided to this function
     *
     * @param string $format
     * @param DateTimeZone|null $timeZone
     * @param string $exceptionMessage
     *
     * @return $this
     */
    public function setStringSourceFormat(
        string       $format,
        DateTimeZone $timeZone = null,
        string       $exceptionMessage = 'The provided string is not in the format {format.raw}'
    ): self
    {
        $this->sourceFormat = $format;
        $this->sourceTimezone = $timeZone;
        $this->sourceFormatException = $exceptionMessage;

        return $this;
    }

    /**
     * @param bool $immutable
     *
     * @return $this
     */
    public function setImmutable(bool $immutable = true): self
    {
        $this->immutabel = $immutable;

        return $this;
    }

    /**
     * Sets the DateTime/DateTimeImmutable object to the specified timezone
     *
     * @param DateTimeZone $timeZone
     *
     * @return $this
     */
    public function setTimezone(DateTimeZone $timeZone): self
    {
        $this->timezone = $timeZone;

        return $this;
    }

    public function parse($value, ?Path $path = null)
    {
        if ($value instanceof \DateTime) {
            $dateTime = $value;
            if ($this->immutable) {
                $dateTime = \DateTimeImmutable::createFromMutable($value);
            }
        } elseif ($value instanceof \DateTimeImmutable) {
            $dateTime = $value;
            if (!$this->immutable) {
                $dateTime = \DateTime::createFromImmutable($value);
            }
        } elseif (is_string($value) && $this->sourceFormat !== null) {
            if ($this->immutable) {
                $dateTime = @\DateTimeImmutable::createFromFormat($this->sourceFormat, $this->sourceTimezone);
            } else {
                $dateTime = @\DateTime::createFromFormat($this->sourceFormat, $this->sourceTimezone);
            }
            if ($dateTime === false) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage(
                        $this->sourceFormatException,
                        [
                            'value' => $value,
                            'format' => $this->sourceFormat,
                        ]
                    ),
                    $path
                );
            }
        } else {
            throw new ParsingException(
                $value,
                Debug::parseMessage(
                    $this->typeExceptionMessage,
                    ['value' => $value]
                ),
                $path
            );
        }

        if ($this->timezone) {
            $dateTime = $dateTime->setTimezone($this->timezone);
        }

        return $dateTime;
    }
}
