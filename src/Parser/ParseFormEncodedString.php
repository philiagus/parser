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
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;


/**
 * Parses the provided string treating it as form encoded data
 * The result of the parser is the parsed data
 *
 * @see parse_str()
 */
class ParseFormEncodedString implements Parser
{
    use Chainable, OverwritableParserDescription, TypeExceptionMessage;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     *
     * @inheritDoc
     */
    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        $value = $builder->getCurrentValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        parse_str($value, $result);

        return $builder->createResult($result);
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not of type string';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'parse form encoded';
    }
}
