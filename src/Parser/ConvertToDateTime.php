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
use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;

class ConvertToDateTime extends Base\Parser
{
    use OverwritableTypeErrorMessage;

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
     * @return static
     * @see ConvertToDateTime::setStringSourceFormat()
     *
     */
    public static function fromSourceFormat(
        string       $format,
        DateTimeZone $timeZone = null,
        string       $exceptionMessage = 'The provided string is not in the format {format.raw}'
    ): static
    {
        return static::new()->setStringSourceFormat($format, $timeZone, $exceptionMessage);
    }

    /**
     * Specifies the source format when receiving a string and trying to convert it to a DateTime/DateTimeImmutable
     * object.
     *
     * The exception message is thrown when the string value does not match the expected format.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
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
    ): static
    {
        $this->sourceFormat = $format;
        $this->sourceTimezone = $timeZone;
        $this->sourceFormatException = $exceptionMessage;

        return $this;
    }

    /**
     * Returns a new instance of this parser
     *
     * @return static
     */
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

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
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
        } elseif ((is_string($value) || is_int($value) || is_float($value)) && $this->sourceFormat !== null) {
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
    public function setTimezone(DateTimeZone $timeZone): static
    {
        $this->timezone = $timeZone;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value could not be converted to DateTime';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'convert to DateTime';
    }
}
