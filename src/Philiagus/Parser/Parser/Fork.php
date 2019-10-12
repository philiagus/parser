<?php
declare(strict_types=1);

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;

class Fork extends Parser
{

    /**
     * @var Parser[]
     */
    private $parsers = [];

    /**
     * Adds a parser to fork the value to without alteration
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
    protected function execute($value, Path $path)
    {
        foreach($this->parsers as $parser) {
            $parser->parse($value, $path);
        }

        return $value;
    }
}