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
 * Whenever this parser is called the value received by this parser is appended to the provided target
 *
 * If the provided target is not an array at that point, Append will convert `null` to an empty array
 *
 * Based on PHP reference rules this parser takes some type possession of the provided target
 *
 * The target can only be `null|array|\ArrayAccess`, where `null` will be internally converted to an empty array on parser creation.
 *
 * @package Parser\Extract
 * @see Contract\Chainable::thenAppendTo()
 */
class Append extends Base\Parser
{
    private array|\ArrayAccess $target;

    /**
     * Append constructor.
     *
     * @param mixed $target
     */
    protected function __construct(null|array|\ArrayAccess &$target)
    {
        $target ??= [];
        $this->target =& $target;
    }

    /**
     * Creates an instance of this parser and sets the value provided to this parser to be appended to
     * the $target
     * Based on PHP reference rules this parser takes type possession of the provided target
     * The target can only be null|array|\ArrayAccess
     *
     * @param \ArrayAccess|array|null $target
     *
     * @return static
     */
    public static function to(null|\ArrayAccess|array &$target): static
    {
        return new static($target);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $this->target[] = $builder->getValue();

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'extract: appended';
    }
}
