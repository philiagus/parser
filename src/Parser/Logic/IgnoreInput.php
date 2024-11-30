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

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;

/**
 * This parser ignores its received value and replaces it with a predefined value
 *
 * @package Parser\Logic
 */
class IgnoreInput extends Base\Parser
{

    protected function __construct(private readonly mixed $value)
    {
    }

    /**
     * Creates a new instance of this parser which will result in the provided value
     *
     * @param mixed $value
     *
     * @return static
     */
    public static function resultIn(mixed $value): static
    {
        return new static($value);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        return $builder->createResult($this->value);
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'replace with fixed value';
    }
}
