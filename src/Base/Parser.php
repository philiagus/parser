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

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Contract\Subject;
use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\Logic\Chain;
use Philiagus\Parser\ResultBuilder;

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

    /**
     * @inheritDoc
     */
    public function parse(Subject $subject): Contract\Result
    {
        return $this->execute(
            $subject->getResultBuilder(
                $this->parserDescription ?? $this->getDefaultParserDescription($subject)
            )
        );
    }

    /**
     * Executes the provided builder and performs the specific parsing
     *
     * @param ResultBuilder $builder
     *
     * @return Contract\Result
     */
    abstract protected function execute(ResultBuilder $builder): Contract\Result;

    /**
     * @see OverwritableParserDescription::getDefaultParserDescription()
     */
    abstract protected function getDefaultParserDescription(Subject $subject): string;

    /**
     * @inheritDoc
     */
    public function thenAssignTo(&$target): Chain
    {
        return $this->then(Assign::to($target));
    }

    /**
     * @inheritDoc
     */
    public function then(ParserContract $parser): Chain
    {
        return Chain::parsers($this, $parser);
    }

    /**
     * @inheritDoc
     */
    public function thenAppendTo(null|\ArrayAccess|array &$target): Chain
    {
        return $this->then(Append::to($target));
    }
}
