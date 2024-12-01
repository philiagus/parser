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

namespace Philiagus\Parser\Parser\Assert;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;


/**
 * Asserts that the provided value is a boolean value, so `true` or `false`
 *
 * @package Parser\Assert
 * @target-type bool
 */
class AssertBoolean extends Base\Parser
{
    use OverwritableTypeErrorMessage;


    protected function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Result
    {
        if (!is_bool($builder->getValue())) {
            $this->logTypeError($builder);
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not a boolean';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'assert boolean';
    }
}
