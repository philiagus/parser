<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser as ParserContract;

class Fork extends Parser
{

    /**
     * @var ParserContract[]
     */
    private $parsers = [];

    /**
     * Adds a parser to fork the value to without alteration
     *
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function addParser(ParserContract $parser): self
    {
        $this->parsers[] = $parser;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        foreach ($this->parsers as $parser) {
            $parser->parse($value, $path);
        }

        return $value;
    }
}