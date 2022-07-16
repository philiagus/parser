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


namespace Philiagus\Parser\Parser\Extraction;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;


/**
 * Stores the value into the provided variable
 */
class Assign implements Parser
{
    use Chainable, OverwritableParserDescription;

    /** @var mixed */
    private mixed $target;

    private function __construct(&$target)
    {
        $this->target = &$target;
    }

    /**
     * Returns a parser that assigns the provided value to the target
     *
     * @param $target
     *
     * @return static
     */
    public static function to(&$target): self
    {
        return new self($target);
    }

    /**
     *
     * @inheritDoc
     */
    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        $this->target = $subject->getValue();

        return $builder->createResultUnchanged();
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'extract: assign';
    }
}
