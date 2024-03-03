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


namespace Philiagus\Parser\Parser\Extract;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;


/**
 * Stores the value into the provided variable, however the desired result
 * of this parser is in most cases accomplished by chaining `thenAssignTo` to any other parser
 * that implements the `Chainable`-Interface (which is most parsers).
 *
 * @package Parser\Extract
 * @see Contract\Chainable::thenAssignTo()
 */
class Assign extends Base\Parser
{

    /** @var mixed */
    private mixed $target;

    private function __construct(&$target)
    {
        $this->target = &$target;
    }

    /**
     * Returns a parser that assigns the provided value to the target when the parser is executed
     *
     * @param $target
     *
     * @return static
     */
    public static function to(&$target): static
    {
        return new static($target);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $this->target = $builder->getValue();

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'extract: assign';
    }
}
