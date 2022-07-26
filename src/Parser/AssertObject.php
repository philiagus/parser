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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;

class AssertObject extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private const DEFAULT_INSTANCEOF_MESSAGE = 'The provided object is not an instance of {class.raw}';

    /** @var callable[] */
    private array $checks = [];

    private function __construct()
    {
    }

    /**
     * Shortcut for creating an instance and calling withInstanceOf
     *
     * @param class-string $class
     * @param string $exceptionMessage
     *
     * @return static
     * @see AssertObject::withInstanceOf()
     */
    public static function instanceOf(
        string $class,
        string $exceptionMessage = self::DEFAULT_INSTANCEOF_MESSAGE
    ): static
    {
        return static::new()->assertInstanceOf($class, $exceptionMessage);
    }

    /**
     * Checks that the object is an instance of the specified class
     *
     *
     * The message of the exception is processed using Debug::parseMessage
     * and receives the following elements:
     * - subject: The object currently being parsed
     * - class: The class the object is not an instance of
     *
     * @param string $class
     * @param string $exceptionMessage
     *
     * @return $this
     */
    public function assertInstanceOf(
        string $class,
        string $exceptionMessage = self::DEFAULT_INSTANCEOF_MESSAGE
    ): static
    {
        $this->checks[] = static function (ResultBuilder $builder, object $value) use ($class, $exceptionMessage): void {
            if (!$value instanceof $class) {
                $builder->logErrorUsingDebug($exceptionMessage, ['class' => $class]);
            }
        };

        return $this;
    }

    /**
     * Creates a new instance of this parser
     *
     * @return static
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * Checks that the object is not an instance of the specified class
     *
     *
     * The message of the exception is processed using Debug::parseMessage
     * and receives the following elements:
     * - subject: The object currently being parsed
     * - class: The class the object is not an instance of
     *
     * @param string $class
     * @param string $exceptionMessage
     *
     * @return $this
     */
    public function assertNotInstanceOf(
        string $class,
        string $exceptionMessage = 'The provided object is an instance of {class.raw}'
    ): static
    {
        $this->checks[] = static function (ResultBuilder $builder, object $value) use ($class, $exceptionMessage): void {
            if ($value instanceof $class) {
                $builder->logErrorUsingDebug($exceptionMessage, ['class' => $class]);
            }
        };

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Contract\Result
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

    protected function getDefaultTypeErrorMessage(): string
    {
        return 'The provided value is not an object';
    }

    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert object';
    }
}
