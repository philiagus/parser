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

namespace Philiagus\Parser\Parser\Convert;

use Philiagus\Parser\Base;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;

class ConvertToArray extends Base\Parser
{

    /**
     * null = using array cast
     * string|int = create array with that key
     * @var string|int|null
     */
    private string|int|null $targetedArrayKey = null;

    private function __construct()
    {
    }

    /**
     * Instructs this parser to convert non-arrays into arrays by using an
     * array cast
     *
     * @return static
     */
    public static function usingCast(): static
    {
        return new static();
    }

    /**
     * Instructs this parser to convert non-arrays into arrays by creating a
     * new array with the defined key and the value of that key being the
     * received subject value
     *
     * @param int|string $key
     *
     * @return static
     */
    public static function creatingArrayWithKey(int|string $key): static
    {
        $instance = new static();
        $instance->targetedArrayKey = $key;

        return $instance;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (is_array($value)) {
            return $builder->createResultUnchanged();
        }

        if ($this->targetedArrayKey !== null) {
            return $builder->createResult([$this->targetedArrayKey => $value]);
        }

        return $builder->createResult((array)$value);
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        if ($this->targetedArrayKey !== null) {
            return "treated as array, if needed with key '$this->targetedArrayKey'";
        }

        return 'treated as array, cast if needed';
    }
}
