<?php
declare(strict_types=1);

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;

class Pipe extends Parser
{
    /**
     * @var Parser[]
     */
    private $parsers = [];

    public function add(Parser $parser): self
    {
        $this->parsers[] = $parser;

        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        foreach($this->parsers as $index => $parser) {
            $value = $parser->parse($value, $path);
        }

        return $value;
    }
}