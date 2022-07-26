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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Contract;

/**
 * A parser that matches any value without further validation
 */
class Any extends Base\Parser
{

    private function __construct()
    {
    }

    /**
     * Return a new instance of this parser
     *
     * @return self
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): \Philiagus\Parser\Contract\Result
    {
        return $builder->createResultUnchanged();
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'accepting any value';
    }
}
