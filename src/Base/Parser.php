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

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Extract\Append;
use Philiagus\Parser\Parser\Extract\Assign;
use Philiagus\Parser\Parser\Logic\Chain;
use Philiagus\Parser\Result;

/**
 * Base class used for parsers to easily write custom parsers by extending this base class.
 *
 * @package Base
 */
abstract class Parser implements Contract\Parser, Contract\Chainable
{

    private string $parserDescription;

    /**
     * @see OverwritableParserDescription::setParserDescription()
     */
    public function setParserDescription(string $description): static
    {
        $this->parserDescription = $description;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] public function parse(Subject $subject): Result
    {
        return $this->execute(
            new ResultBuilder($subject, $this->parserDescription ?? $this->getDefaultParserDescription($subject)),
        );
    }

    /**
     * Executes the provided builder and performs the specific parsing
     *
     * @param ResultBuilder $builder
     *
     * @return Result
     * @throws ParsingException
     */
    abstract protected function execute(ResultBuilder $builder): Result;

    /**
     * @see OverwritableParserDescription::getDefaultParserDescription()
     */
    abstract protected function getDefaultParserDescription(Subject $subject): string;

    /** @inheritDoc */
    #[\Override] public function thenAssignTo(&$target): Chain
    {
        return $this->then(Assign::to($target));
    }

    /** @inheritDoc */
    #[\Override] public function then(ParserContract $parser): Chain
    {
        return Chain::parsers($this, $parser);
    }

    /** @inheritDoc */
    #[\Override] public function thenAppendTo(null|\ArrayAccess|array &$target): Chain
    {
        return $this->then(Append::to($target));
    }
}
