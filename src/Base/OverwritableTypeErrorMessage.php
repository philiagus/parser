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

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Error as ErrorImplementation;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Stringify;

/**
 * Trait to easily implement overwritable error messages type error messages in parsers
 *
 * For example when writing a parser that only allows integers, your code could look like this:
 * ```php
 * if (!is_int($builder->getValue())) {
 *     $this->logTypeError($builder);
 *     return $builder->createResultUnchanged();
 * }
 * ```
 *
 * @package Base
 */
trait OverwritableTypeErrorMessage
{

    private ?string $typeErrorMessage = null;

    /**
     * Defines the error message to use if the value is not of the expected type
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Stringify::parseMessage()
     *
     */
    public function setTypeErrorMessage(string $message): static
    {
        $this->typeErrorMessage = $message;

        return $this;
    }

    /**
     * Use this method from within the parsers to log a type error once detected into
     * the ResultBuilder. This is the preferred way of using this trait from inside the parser
     *
     * @param ResultBuilder $builder
     *
     * @throws ParsingException
     */
    protected function logTypeError(ResultBuilder $builder): void
    {
        $builder->logError($this->getTypeError($builder->getSubject()));
    }

    /**
     * Creates an Error object for the provided subject which represents a type error
     * Please add this error to the returned response or throw it directly, if throwOnError
     * is set for the Subject provided. This method does not throw the error outright and only
     * creates it as you might want to alter this throw behaviour in your parser for some reason
     *
     * @param Subject $subject
     *
     * @return ErrorImplementation
     */
    protected function getTypeError(Subject $subject): ErrorImplementation
    {
        return new ErrorImplementation(
            $subject,
            Stringify::parseMessage(
                $this->typeErrorMessage ?? $this->getDefaultTypeErrorMessage(),
                ['value' => $subject->getValue()]
            )
        );
    }

    /**
     * Provide the default type error message used to create the error in logTypeError or getTypeError
     * if the message has not been overwritten
     *
     * @return string
     * @see logTypeError()
     * @see getTypeError()
     */
    abstract protected function getDefaultTypeErrorMessage(): string;

}
