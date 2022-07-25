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
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;


/**
 * Class BooleanPrimitive
 *
 * @package Philiagus\Parser
 */
class AssertBoolean extends Base\Parser
{
    use OverwritableTypeErrorMessage;


    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function execute(ResultBuilder $builder): Result
    {
        if (!is_bool($builder->getValue())) {
            $this->logTypeError($builder);
        }

        return $builder->createResultUnchanged();
    }

    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not a boolean';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'assert boolean';
    }
}
