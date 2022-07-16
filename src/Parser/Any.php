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

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;


class Any implements Parser
{
    use Chainable, OverwritableParserDescription;

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
     * @param Subject $subject
     *
     * @return Result
     */
    public function parse(Subject $subject): Result
    {
        return $this->createResultBuilder($subject)->createResultUnchanged();
    }

    /**
     * @param Subject $subject
     *
     * @return string
     */
    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'accepting any value';
    }
}
