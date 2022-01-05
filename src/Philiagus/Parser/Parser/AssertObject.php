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

class AssertObject implements ChainableParser
{
    use Chainable;

    private const DEFAULT_INSTANCEOF_MESSAGE = 'The provided object is not an instance of {class.raw}';

    /** @var callable[] */
    private array $checks = [];

    private string $typeExceptionMessage = 'The provided value is not an object';

    private function __construct()
    {
    }

    public static function new(string $typeExceptionMessage = 'The provided value is not an object'): self
    {
        return (new self())
            ->setTypeExceptionMessage($typeExceptionMessage);
    }

    /**
     * Shortcut for creating an instance and calling withInstanceOf
     *
     * @param string $class
     * @param string $exceptionMessage
     *
     * @return static
     * @see AssertObject::withInstanceOf()
     */
    public static function instanceOf(
        string $class,
        string $exceptionMessage = self::DEFAULT_INSTANCEOF_MESSAGE
    ): self
    {
        return self::new()->assertInstanceOf($class, $exceptionMessage);
    }

    /**
     * Checks that the object is an instance of the specified class. On mismatch an exception is thrown.
     *
     *
     * The message of the exception is processed using Debug::parseMessage
     * and receives the following elements:
     * - value: The object currently being parsed
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
    ): self
    {
        $this->checks[] = function (object $value, ?Path $path) use ($class, $exceptionMessage): void {
            if (!$value instanceof $class) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage(
                        $exceptionMessage,
                        [
                            'value' => $value,
                            'class' => $class,
                        ]
                    ),
                    $path
                );
            }
        };

        return $this;
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

    public function parse($value, ?Path $path = null)
    {
        if (!is_object($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage(
                    $this->typeExceptionMessage,
                    [
                        'value' => $value,
                    ]
                ),
                $path
            );
        }
        foreach ($this->checks as $check) {
            $check($value, $path);
        }

        return $value;
    }
}
