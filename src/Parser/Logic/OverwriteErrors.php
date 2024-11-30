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

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Error;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Stringify;

/**
 * This parser catches the parsing errors generate by the child parser and overwrite the
 * error with a new Error that has a provided message
 * This is used to create more meaningful error messages for consumers of the parser
 * This also means, that the subject of the resulting error is the subject provided
 * to this OverwriteErrors parser rather than the subject that any caught error might
 * originate from.
 *
 * @package Parser\Logic
 */
class OverwriteErrors extends Base\Parser
{

    private string $message;
    private Parser $parser;

    protected function __construct(string $message, Parser $parser)
    {
        $this->message = $message;
        $this->parser = $parser;
    }

    /**
     * Creates the OverwriteError parser with a defined error message and a parser
     * If the parser results in or throws an error, the error is caught and
     * a new error is created, receiving the errors of the parser as sourceErrors
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     * @param Parser $around
     *
     * @return static
     * @see Stringify::parseMessage()
     * @see Error::getSourceErrors()
     */
    public static function withMessage(string $message, Parser $around): static
    {
        return new static($message, $around);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        /** @var Error[] $errors */
        $errors = [];
        try {
            $result = $this->parser->parse($builder->getSubject());
            if ($result->isSuccess()) {
                return $builder->createResultFromResult($result);
            }
            $errors = $result->getErrors();
        } catch (ParsingException $exception) {
            $errors[] = $exception->getError();
        }

        $builder->logErrorStringify($this->message, [], null, $errors);

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return '';
    }
}
