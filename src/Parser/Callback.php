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
use Philiagus\Parser\Contract;
use Philiagus\Parser\Error;
use Philiagus\Parser\ResultBuilder;

/**
 * A parser that simplifies single-use cases where normally an entire parser would have been written
 * This parser takes a closure with signature \Closure(mixed, Subject): mixed
 * If this closure throws an error the parser will convert that exception to an Error and log
 * it correspondingly, honoring the current parser mode (throw mode or gather mode)
 *
 * On no error the result of this parser is the result of the closure
 */
class Callback extends Parser {

    private function __construct(
        private readonly \Closure $closure,
        private readonly string $description
    )  {}

    /**
     * The Provided closure will be called with two elements: The value and the
     * subject currently being parsed. The value is already extracted for convenience
     * If this method throws an exception that exception is treated as an error and treated correctly
     * (added to the list of errors in gather mode or thrown as error on throw mode)
     *
     * If the \Closure does not produce an exception the return value is treated as the result
     * of this parser
     *
     * @param \Closure(mixed, Contract\Subject): mixed $closure
     * @return self
     */
    public static function new(\Closure $closure, string $description = ''): self
    {
        return new self($closure, $description);
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
                new Error($subject, $e->getMessage(), $e)
            );

            return $builder->createResultUnchanged();
        }

        return $builder->createResult($result, $this->description);
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'callback';
    }
}
