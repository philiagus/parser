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

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\ChainableParser;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;

class Chain implements ChainableParser
{

    /** @var Parser[] */
    private array $parsers;

    private function __construct(Parser ...$parsers)
    {
        $this->parsers = $parsers;
    }

    public static function parsers(Parser ...$parsers): self
    {
        return new self(...$parsers);
    }

    /**
     * @inheritDoc
     */
    public function thenAppendTo(&$target): self
    {
        $this->parsers[] = Append::to($target);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function then(Parser $parser): self
    {
        $this->parsers[] = $parser;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function thenAssignTo(&$target): self
    {
        $this->parsers[] = Assign::to($target);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function parse($value, Path $path = null)
    {
        foreach ($this->parsers as $parser) {
            $value = $parser->parse($value, $path);
        }

        return $value;
    }
}
