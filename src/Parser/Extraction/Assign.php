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


namespace Philiagus\Parser\Parser\Extraction;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;


/**
 * Stores the value into the provided variable
 */
class Assign extends Base\Parser
{
    use OverwritableParserDescription;

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
