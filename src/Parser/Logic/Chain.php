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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Result;


/**
 * Parser used to chain multiple parsers after one another, feeding the result of the previous parser
 * to the next. The chain is broken when a parsers result has errors.
 */
class Chain implements Contract\Parser, Contract\Chainable
{
    use Chainable;

    /** @var Contract\Parser[] */
    private array $parsers;

    private function __construct(Contract\Parser $parser, Contract\Parser ...$parsers)
    {
        $this->parsers = [$parser, ...$parsers];
    }

    /**
     * Chains the provided list of parsers after one another, feeding the result of the previous parser
     * to the next. The chain is broken when a parsers result has errors.
     * @param Contract\Parser $parser
     * @param Contract\Parser ...$parsers
     *
     * @return static
     */
    public static function parsers(Contract\Parser $parser, Contract\Parser ...$parsers): static
    {
        return new static($parser, ...$parsers);
    }

    /**
     * @inheritDoc
     */
    public function parse(Contract\Subject $subject): Contract\Result
    {
        foreach ($this->parsers as $parser) {
            $subject = $parser->parse($subject);
            if (!$subject->isSuccess()) return $subject;
        }

        return $subject;
    }

    /**
     * @inheritDoc
     */
    public function then(Contract\Parser $parser): Chain
    {
        $this->parsers[] = $parser;

        return $this;
    }
}
