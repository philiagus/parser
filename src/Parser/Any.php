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


class Any extends Base\Parser
{

    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Result
    {
        return $builder->createResultUnchanged();
    }

    /**
     * @param Subject $subject
     *
     * @return string
     */
    protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'accepting any value';
    }
}
