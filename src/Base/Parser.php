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
use Philiagus\Parser\Parser\Extraction\Append;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Parser\Logic\Chain;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

abstract class Parser implements Contract\Parser, Contract\Chainable
{

    private string $chainDescription;

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setChainDescription(string $description): self
    {
        $this->chainDescription = $description;

        return $this;
    }

    public function parse(Subject $subject): Result
    {
        return $this->execute(
            $subject->getResultBuilder(
                Debug::parseMessage($this->chainDescription ?? $this->getDefaultChainDescription($subject),
                    ['subject' => $subject->getValue()])
            )
        );
    }

    /**
     * @param ResultBuilder $builder
     *
     * @return Result
     */
    abstract protected function execute(ResultBuilder $builder): Result;

    /**
     * @param Subject $subject
     *
     * @return string
     */
    abstract protected function getDefaultChainDescription(Subject $subject): string;

    public function thenAssignTo(&$target): Chain
    {
        return $this->then(Assign::to($target));
    }

    /**
     * Chains another parser to use the result of the current parser
     *
     * @param ParserContract $parser
     *
     * @return Chain
     */
    public function then(ParserContract $parser): Chain
    {
        return Chain::parsers($this, $parser);
    }

    public function thenAppendTo(&$target): Chain
    {
        return $this->then(Append::to($target));
    }
}
