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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\Utility\Forwarded;


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
     * @param Parser ...$parsers
     *
     * @return static
     */
    public static function to(Parser ...$parsers): self
    {
        return new self(...$parsers);
    }

    /**
     * Adds a parser to fork the value to without alteration
     *
     * @param Parser $parser
     *
     * @return $this
     */
    public function add(Parser $parser): self
    {
        $this->parsers[] = $parser;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Result
    {
        foreach ($this->parsers as $index => $parser) {
            $builder->incorporateResult(
                $parser->parse(new Forwarded($builder->getSubject(), "fork #$index"))
            );
        }

        return $builder->createResultUnchanged();
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'fork to multiple parsers';
    }
}
