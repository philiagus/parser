<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Parser\Convert;

use DateTimeZone;
use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;

/**
 * Converts the received value to a \DateTime or \DateTimeImmutable if it isn't already
 *
 * @package Parser\Convert
 * @see \DateTime
 * @see \DateTimeImmutable
 * @target-type string|\DateTimeInterface -> \DateTime|\DateTimeInterface
 */
class ConvertToDateTime extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private ?string $sourceFormat = null;
    private ?DateTimeZone $sourceTimezone = null;
    private string $sourceFormatException = '';
    private bool $immutable = false;
    private ?DateTimeZone $timezone = null;

    protected function __construct()
    {
    }

    /**
     * Is identical to creating a new instance of this class and calling setStringSourceFormat on it
     *
     * @param string $format
     * @param DateTimeZone|null $timeZone
     * @param string $errorMessage
     *
     * @return static
     * @see ConvertToDateTime::setStringSourceFormat()
     *
     */
    public static function fromSourceFormat(
        string       $format,
        DateTimeZone $timeZone = null,
        string       $errorMessage = 'The provided string is not in the format {format.raw}'
    ): static
    {
        return static::new()->setStringSourceFormat($format, $timeZone, $errorMessage);
    }

    /**
     * Specifies the source format when receiving a string and trying to convert it to a DateTime/DateTimeImmutable
     * object.
     *
     * The error message is used when the string value does not match the expected format.
     *
     * The error message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - format: The format provided to this function
     *
     * @param string $format
     * @param DateTimeZone|null $timeZone
     * @param string $errorMessage
     *
     * @return $this
     */
    public function setStringSourceFormat(
        string       $format,
        DateTimeZone $timeZone = null,
        string       $errorMessage = 'The provided string is not in the format {format.raw}'
    ): static
    {
        $this->sourceFormat = $format;
        $this->sourceTimezone = $timeZone;
        $this->sourceFormatException = $errorMessage;

        return $this;
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * @param bool $immutable
     *
     * @return $this
     */
    public function setImmutable(bool $immutable = true): static
    {
        $this->immutable = $immutable;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        $dateTime = null;
        if ($value instanceof \DateTimeInterface) {
            if ($this->immutable) {
                $dateTime = \DateTimeImmutable::createFromInterface($value);
            } else {
                $dateTime = \DateTime::createFromInterface($value);
            }
        } elseif ((is_string($value) || is_int($value) || is_float($value)) && $this->sourceFormat !== null) {
            if ($this->immutable) {
                $dateTime = @\DateTimeImmutable::createFromFormat($this->sourceFormat, (string)$value, $this->sourceTimezone);
            } else {
                $dateTime = @\DateTime::createFromFormat($this->sourceFormat, (string)$value, $this->sourceTimezone);
            }
            if ($dateTime === false) {
                $builder->logErrorStringify(
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
    public function setTimezone(DateTimeZone $timeZone): static
    {
        $this->timezone = $timeZone;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value could not be converted to DateTime';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'convert to DateTime';
    }
}
