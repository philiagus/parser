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
use Philiagus\Parser\Parser\Logic\Fail;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Contract;

/**
 * Class Fixed
 *
 * The Fixed parser ignores its received value and replaces it with a predefined value
 *
 * @package Philiagus\Parser\Parser
 */
class IgnoreInput extends Base\Parser
{

    /** @var mixed */
    private mixed $value;

    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param mixed $value
     *
     * @return self
     */
    public static function resultIn(mixed $value): self
    {
        return new self($value);
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): \Philiagus\Parser\Contract\Result
    {
        return $builder->createResult($this->value);
    }

    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'replace with fixed value';
    }
}
