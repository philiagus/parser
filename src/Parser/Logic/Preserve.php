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
use Philiagus\Parser\Contract\Parser as ParserContract;

/**
 * Preserves a value around another parser, shielding it from alteration
 */
class Preserve implements Parser
{
    use Chainable, OverwritableChainDescription;

    /** @var ParserContract */
    private ParserContract $around;

    /**
     * Preserve constructor.
     *
     * @param ParserContract $around
     */
    private function __construct(ParserContract $around)
    {
        $this->around = $around;
    }

    /**
     * @param ParserContract $parser
     *
     * @return static
     */
    public static function around(ParserContract $parser): self
    {
        return new self($parser);
    }

    public function parse($value, Path $path = null)
    {
        $this->around->parse($value, $path->chain('inside preserve', false));

        return $value;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('preserved', false);
    }
}
