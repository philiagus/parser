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
    public function setTypeErrorMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * @param ResultBuilder $builder
     *
     * @throws ParsingException
     */
    protected function logTypeError(ResultBuilder $builder): void
    {
        $builder->logError($this->getTypeError($builder->getSubject()));
    }

    /**
     * @return string
     */
    abstract protected function getDefaultTypeErrorMessage(): string;

    /**
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

}
