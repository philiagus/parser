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

trait TypeExceptionMessage
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
    public function setTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * @param ResultBuilder $builder
     *
     * @throws ParsingException
     */
    private function logTypeError(ResultBuilder $builder): void
    {
        $builder->logErrorUsingDebug($this->typeExceptionMessage ?? $this->getDefaultTypeExceptionMessage());
    }

    /**
     * @return string
     */
    abstract protected function getDefaultTypeExceptionMessage(): string;

}
