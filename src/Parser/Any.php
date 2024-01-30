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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;

/**
 * A parser that matches any value without further validation
 */
class Any extends Base\Parser
{

    private function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'accepting any value';
    }
}
