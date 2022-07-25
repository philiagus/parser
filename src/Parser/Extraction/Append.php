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

use ArrayAccess;
use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;

/**
 * Whenever this parser is called the value received by this parser is appended to the provided target
 * If the provided target is not an array at that point, Append will convert `null` to an empty array and
 * in all other cases throw a ParserConfigurationException
 */
class Append extends Base\Parser
{

    /** @var null|array|ArrayAccess */
    private null|array|ArrayAccess $target;

    /**
     * Append constructor.
     *
     * @param mixed $target
     */
    private function __construct(null|array|ArrayAccess &$target)
    {
        $this->target =& $target;
    }

    /**
     * @param ArrayAccess|array|null $target
     *
     * @return static
     */
    public static function to(null|ArrayAccess|array &$target): self
    {
        return new self($target);
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Result
    {
        $this->target[] = $builder->getValue();

        return $builder->createResultUnchanged();
    }

    protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'extract: appended';
    }
}
