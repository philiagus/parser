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
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class ConvertToDateTime implements Parser
{
    use Chainable, OverwritableParserDescription, TypeExceptionMessage;

    private ?string $sourceFormat = null;
    private ?DateTimeZone $sourceTimezone = null;
    private string $sourceFormatException = '';
    private bool $immutable = false;
    private ?DateTimeZone $timezone = null;

    private function __construct()
    {

    }

    /**
     * Is identical to creating a new instance of this class and calling setStringSourceFormat on it
     *
     * @param string $format
     * @param DateTimeZone|null $timeZone
     * @param string $exceptionMessage
     *
     * @return self
     * @see ConvertToDateTime::setStringSourceFormat()
     *
     */
    public static function fromSourceFormat(
        string       $format,
        DateTimeZone $timeZone = null,
        string       $exceptionMessage = 'The provided string is not in the format {format.raw}'
    ): self
    {
        return self::new()->setStringSourceFormat($format, $timeZone, $exceptionMessage);
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

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param bool $immutable
     *
     * @return $this
     */
    public function setImmutable(bool $immutable = true): self
    {
        $this->immutable = $immutable;

        return $this;
    }

    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        $value = $builder->getCurrentValue();
        $dateTime = null;
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
        } elseif ((is_string($value) || is_int($value)) && $this->sourceFormat !== null) {
            if ($this->immutable) {
                $dateTime = @\DateTimeImmutable::createFromFormat($this->sourceFormat, (string) $value, $this->sourceTimezone);
            } else {
                $dateTime = @\DateTime::createFromFormat($this->sourceFormat, (string) $value, $this->sourceTimezone);
            }
            if ($dateTime === false) {
                $builder->logErrorUsingDebug(
                    $this->sourceFormatException,
                    ['format' => $this->sourceFormat]
                );
            }
        } else {
            $this->logTypeError($builder);
        }

        if ($dateTime && $this->timezone) {
            $dateTime = $dateTime->setTimezone($this->timezone);
        }

        return $builder->createResult($dateTime);
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

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value could not be converted to DateTime';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'convert to DateTime';
    }
}
