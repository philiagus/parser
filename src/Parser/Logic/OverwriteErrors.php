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

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class OverwriteErrors implements Parser
{
    use Chainable, OverwritableParserDescription;

    /** @var string */
    private string $message;

    /** @var Parser */
    private Parser $parser;

    private function __construct(string $message, Parser $parser)
    {
        $this->message = $message;
        $this->parser = $parser;
    }

    public static function withMessage(string $message, Parser $around)
    {
        return new self($message, $around);
    }

    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        /** @var Error[] $errors */
        $errors = [];
        try {
            $result = $this->parser->parse($subject);
            if($result->isSuccess()) {
                return $builder->createResultFromResult($result);
            }
            $errors = $result->getErrors();
        } catch (ParsingException $exception) {
            $errors[] = $exception->getError();
        }

        $builder->logErrorUsingDebug(
            $this->message,
            [],
            null,
            $errors
        );

        return $builder->createResultUnchanged();
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return '';
    }
}
