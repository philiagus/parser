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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Exception\ParserConfigurationException;

/**
 * Parser used to convert a value to an element of a PHP enum
 * Matching can be performed by enum name, backed value or both
 *
 * @package Parser\Convert
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @target-type mixed -> \UnitEnum
 */
class ConvertToEnum extends Base\Parser
{
    use Base\OverwritableTypeErrorMessage;

    private string $notFoundMessage = "The value is outside the limits of the expected enum";

    /**
     * @param class-string<\UnitEnum> $className
     * @param bool|null $nameFirst
     * @param bool|null $valueFirst
     */
    private function __construct(
        private readonly string $className,
        private readonly ?bool  $nameFirst,
        private readonly ?bool  $valueFirst
    )
    {
        if (!enum_exists($this->className)) {
            throw new ParserConfigurationException(
                "Trying to convert to not existing enum class $this->className"
            );
        }
        if ($this->valueFirst !== null && !is_a($this->className, \BackedEnum::class, true)) {
            throw new ParserConfigurationException(
                "Trying to convert to enum class $this->className using value, but it's not a backed enum"
            );
        }
    }

    /**
     * Creates the ConvertToEnum configured to match the received string against the name of the available enum
     * values
     *
     * @param class-string<\UnitEnum> $class
     *
     * @return static
     */
    public static function byName(string $class): self
    {
        return new self($class, true, null);
    }

    /**
     * Create the ConvertToEnum configured to match the received string or int against the backed value of the available
     * enum values. A backed enum must be provided for this method.
     *
     * @param class-string<\BackedEnum> $class
     *
     * @return static
     */
    public static function byValue(string $class): self
    {
        return new self($class, null, true);
    }

    /**
     * Create the ConvertToEnum configured to match the received string or int first against the name and then against
     * the backed value of the available enum values. A backed enum must be provided for this method.
     *
     * @param class-string<\BackedEnum> $class
     *
     * @return static
     */
    public static function byNameThenValue(string $class): self
    {
        return new self($class, true, false);
    }

    /**
     * Create the ConvertToEnum configured to match the received string or int first against the backed value and then
     * against the name of the available enum values. A backed enum must be provided for this method.
     *
     * @param class-string<\BackedEnum> $class
     *
     * @return static
     */
    public static function byValueThenName(string $class): self
    {
        return new self($class, false, true);
    }

    /**
     * Sets the error message to create when the provided value was not found among the available values
     * of the enum class
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Stringify::parseMessage()
     */
    public function setNotFoundErrorMessage(string $message): self
    {
        $this->notFoundMessage = $message;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();

        if ($value instanceof $this->className) {
            return $builder->createResultUnchanged();
        }

        if (
            !is_string($value) && ($this->valueFirst === null || !is_int($value))
        ) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        $result = ($this->nameFirst === true ? $this->executeByName($value) : null)
            ?? ($this->valueFirst === true ? $this->executeByValue($value) : null)
            ?? ($this->nameFirst !== null ? $this->executeByName($value) : null)
            ?? ($this->valueFirst !== null ? $this->executeByValue($value) : null);

        if ($result !== null) {
            return $builder->createResult($result);
        }

        $builder->logErrorStringify($this->notFoundMessage);

        return $builder->createResultUnchanged();
    }

    /**
     * @param int|string $name
     *
     * @return \UnitEnum|null
     */
    private function executeByName(int|string $name): ?\UnitEnum
    {
        if (is_int($name)) return null;
        /** @var \UnitEnum $value */
        /** @noinspection PhpUndefinedMethodInspection */
        foreach (($this->className)::cases() as $value) {
            if ($value->name === $name) {
                return $value;
            }
        }

        return null;
    }

    private function executeByValue(mixed $value): ?\BackedEnum
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return ($this->className)::tryFrom($value);
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'ConvertToEnum';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        if ($this->valueFirst !== null) {
            return "The provided value is not an integer or string, {subject.type} received";
        }

        return "The provided value is not a string, {subject.type} received";
    }
}
