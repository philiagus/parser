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


class Chain implements Contract\Parser, Contract\Chainable
{
    use Chainable;

    /** @var Contract\Parser[] */
    private array $parsers;

    private function __construct(Contract\Parser $parser, Contract\Parser ...$parsers)
    {
        $this->parsers = [$parser, ...$parsers];
    }

    public static function parsers(Contract\Parser $parser, Contract\Parser ...$parsers): self
    {
        return new self($parser, ...$parsers);
    }

    /**
     * @inheritDoc
     */
    public function parse(Subject $subject): Result
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
