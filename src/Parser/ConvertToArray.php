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

class ConvertToArray extends Base\Parser
{

    private string|int|null $targetedArrayKey = null;

    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function usingCast(): self
    {
        return new self();
    }

    /**
     * @param int|string $key
     *
     * @return static
     */
    public static function creatingArrayWithKey(int|string $key): self
    {
        $instance = new self();
        $instance->targetedArrayKey = $key;

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        if (is_array($value)) {
            return $builder->createResultUnchanged();
        }

        if ($this->targetedArrayKey !== null) {
            return $builder->createResult([$this->targetedArrayKey => $value]);
        }

        return $builder->createResult((array) $value);
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        if ($this->targetedArrayKey !== null) {
            return "treated as array, if needed with key '$this->targetedArrayKey'";
        }

        return 'treated as array, cast if needed';
    }
}
