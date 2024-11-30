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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Error;
use Philiagus\Parser\Util\Stringify;

/**
 * **Target Type**: mixed
 *
 * A parser that simplifies single-use cases where normally an entire parser would have been written
 *
 * This parser takes a closure with signature `\Closure(mixed, Subject): mixed`
 *
 * If this closure throws an error the parser will convert that exception to an Error and log
 * it correspondingly, honoring the current parser mode (throw mode or gather mode)
 *
 * On no error the result of this parser is the result of the closure
 *
 * @see Callback::new()
 * @package Parser\Generic
 */
class Callback extends Parser
{
    private ?string $errorMessage = null;

    protected function __construct(
        private readonly \Closure $closure,
        private readonly string   $description
    )
    {
    }

    /**
     * The Provided closure will be called with two elements: The value and the
     * subject currently being parsed. The value is already extracted for convenience
     *
     * If this method throws an exception that exception is treated as an error and treated correctly
     * (added to the list of errors in gather mode or thrown as error on throw mode)
     *
     * If the \Closure does not throw a \Throwable the return value is treated as the result
     * of this parser
     *
     * @param \Closure(mixed, Contract\Subject): mixed $closure
     * @param string $description Can be used to overwrite the description of this parser in the
     *                                 utility parser chain
     * @return self
     */
    public static function new(\Closure $closure, string $description = 'callback'): static
    {
        return new static($closure, $description);
    }

    /**
     * Overwrites the error message used when the callback results in an error
     *
     * The error message is processed using Stringify::parseMessage and receives the following replacers:
     * - value: The value provided to the closure
     * - throwable: The \Throwable object thrown by the closure
     * - throwableMessage: The \Throwable message thrown by the closure
     * - throwableCode: The code of the \Throwable thrown by the closure
     * - throwableLine: The line of the \Throwable thrown by the closure
     * - throwableFile: The file of the \Throwable thrown by the closure
     *
     * @param string $message
     * @return $this
     * @see Stringify::parseMessage()
     * @see \Throwable
     */
    public function setErrorMessage(string $message): static
    {
        $this->errorMessage = $message;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $subject = $builder->getSubject();
        $value = $builder->getValue();
        try {
            $result = ($this->closure)($value, $subject);
        } catch (\Throwable $e) {
            $builder->logError(
                isset($this->errorMessage) ?
                    Error::createUsingStringify(
                        $subject,
                        $this->errorMessage,
                        [
                            'throwable' => $e,
                            'throwableMessage' => $e->getMessage(),
                            'throwableCode' => $e->getCode(),
                            'throwableLine' => $e->getLine(),
                            'throwableFile' => $e->getFile(),
                        ],
                        sourceThrowable: $e
                    ) :
                    new Error($subject, $e->getMessage(), $e)
            );

            return $builder->createResultUnchanged();
        }

        return $builder->createResult($result);
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return $this->description;
    }
}
