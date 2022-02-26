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
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;

class Fork implements Parser
{
    use Chainable, OverwritableChainDescription;

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
    public function addParser(Parser $parser): self
    {
        $this->parsers[] = $parser;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function parse($value, Path $path = null)
    {
        foreach ($this->parsers as $parser) {
            $parser->parse($value, $path);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultChainPath(Path $path): Path
    {
        return $path;
    }
}
