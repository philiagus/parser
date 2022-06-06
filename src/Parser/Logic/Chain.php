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
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;

class Chain implements Parser
{

    use Chainable;

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
    public function parse($value, Path $path = null)
    {
        $lastKey = count($this->parsers) - 1;
        $path ??= Path::default($value);
        foreach ($this->parsers as $index => $parser) {
            $value = $parser->parse($value, $path);
            if ($index !== $lastKey) {
                $path = $parser->getChainedPath($path);
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getChainedPath(Path $path): Path
    {
        $result = $path;
        foreach ($this->parsers as $parser) {
            $result = $parser->getChainedPath($result);
        }

        return $result;
    }
}
