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
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertObject implements Parser
{
    use Chainable, OverwritableChainDescription, TypeExceptionMessage;

    private const DEFAULT_INSTANCEOF_MESSAGE = 'The provided object is not an instance of {class.raw}';

    /** @var callable[] */
    private array $checks = [];

    private function __construct()
    {
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

    public static function new(string $typeExceptionMessage = 'The provided value is not an object'): self
    {
        return (new self())
            ->setTypeExceptionMessage($typeExceptionMessage);
    }

    public function parse($value, ?Path $path = null)
    {
        if (!is_object($value)) {
            $this->throwTypeException($value, $path);
        }
        foreach ($this->checks as $check) {
            $check($value, $path);
        }

        return $value;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'The provided value is not an object';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert object', false);
    }
}
