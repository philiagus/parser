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
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\Utility\Forwarded;

/**
 * Forks out the received subject to multiple other parsers
 * The result of this parser is identical to the received value, even
 * if any of the provided parsers changes the value
 */
class Fork extends Base\Parser
{

    /** @var Parser[] */
    private array $parsers;

    /**
     * Fork constructor.
     *
     * @param Parser ...$parsers
     */
    private function __construct(Parser ...$parsers)
    {
        $this->parsers = $parsers;
    }

    /**
     * Creates this parser with a list of other parsers to fork the value to
     *
     * @param Parser ...$parsers
     *
     * @return static
     */
    public static function to(Parser ...$parsers): static
    {
        return new static(...$parsers);
    }

    /**
     * Adds a parser to fork the value to
     *
     * @param Parser $parser
     *
     * @return $this
     */
    public function add(Parser $parser): static
    {
        $this->parsers[] = $parser;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        foreach ($this->parsers as $index => $parser) {
            $builder->incorporateResult(
                $parser->parse(new Forwarded($builder->getSubject(), "fork #$index"))
            );
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'fork to multiple parsers';
    }
}
