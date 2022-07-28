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
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
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
     * @return static
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Contract\Result
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

    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert boolean';
    }
}
