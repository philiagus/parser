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

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Contract\Subject;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

trait OverwritableTypeErrorMessage
{

    private ?string $typeExceptionMessage = null;

    /**
     * Defines the exception message to use if the value is not a string
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function setTypeErrorMessage(string $message): static
    {
        $this->typeExceptionMessage = $message;

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
     * @return Error
     */
    protected function getTypeError(Subject $subject): Error
    {
        return new Error(
            $subject,
            Debug::parseMessage(
                $this->typeExceptionMessage ?? $this->getDefaultTypeErrorMessage(),
                ['subject' => $subject->getValue()]
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
