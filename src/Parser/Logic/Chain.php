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
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Result;


class Chain implements Parser
{

    use Chainable;

    /** @var Parser[] */
    private array $parsers;

    private function __construct(Parser $parser, Parser ...$parsers)
    {
        $this->parsers = [$parser, ...$parsers];
    }

    public static function parsers(Parser $parser, Parser ...$parsers): self
    {
        return new self($parser, ...$parsers);
    }

    /**
     * @inheritDoc
     */
    public function parse(Subject $subject): Result
    {
        foreach ($this->parsers as $parser) {
            $result = $parser->parse($subject);
            if (!$result->isSuccess()) return $result;
            $subject = $result->subjectChain();
        }

        return $result;
    }
}
