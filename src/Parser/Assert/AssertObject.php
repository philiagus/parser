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

namespace Philiagus\Parser\Parser\Assert;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;

/**
 * Asserts the value to be an object
 *
 * @package Parser\Assert
 * @target-type object
 */
class AssertObject extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private const string DEFAULT_INSTANCEOF_MESSAGE = 'The provided object is not an instance of {class.raw}';

    /** @var callable[] */
    private array $checks = [];

    protected function __construct()
    {
    }

    /**
     * Shortcut for creating an instance and calling withInstanceOf
     *
     * @param class-string $class
     * @param string $errorMessage
     *
     * @return static
     * @see AssertObject::withInstanceOf()
     */
    public static function instanceOf(
        string $class,
        string $errorMessage = self::DEFAULT_INSTANCEOF_MESSAGE
    ): static
    {
        return static::new()->assertInstanceOf($class, $errorMessage);
    }

    /**
     * Checks that the object is an instance of the specified class
     *
     *
     * The message of the exception is processed using Stringify::parseMessage
     * and receives the following elements:
     * - value: The object currently being parsed
     * - class: The class the object is not an instance of
     *
     * @param string $class
     * @param string $errorMessage
     *
     * @return $this
     */
    public function assertInstanceOf(
        string $class,
        string $errorMessage = self::DEFAULT_INSTANCEOF_MESSAGE
    ): static
    {
        $this->checks[] = static function (ResultBuilder $builder, object $value) use ($class, $errorMessage): void {
            if (!$value instanceof $class) {
                $builder->logErrorStringify($errorMessage, ['class' => $class]);
            }
        };

        return $this;
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Checks that the object is not an instance of the specified class
     *
     *
     * The message of the exception is processed using Stringify::parseMessage
     * and receives the following elements:
     * - value: The object currently being parsed
     * - class: The class the object is not an instance of
     *
     * @param string $class
     * @param string $errorMessage
     *
     * @return $this
     */
    public function assertNotInstanceOf(
        string $class,
        string $errorMessage = 'The provided object is an instance of {class.raw}'
    ): static
    {
        $this->checks[] = static function (ResultBuilder $builder, object $value) use ($class, $errorMessage): void {
            if ($value instanceof $class) {
                $builder->logErrorStringify($errorMessage, ['class' => $class]);
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_object($builder->getValue())) {
            $this->logTypeError($builder);
        } else {
            foreach ($this->checks as $check) {
                $check($builder, $value);
            }
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'The provided value is not an object';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert object';
    }
}
