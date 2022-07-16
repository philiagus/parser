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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Result;

class ConvertToArray implements Parser
{
    use Chainable, OverwritableParserDescription;

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
     * @param $key
     *
     * @return static
     * @throws ParserConfigurationException
     */
    public static function creatingArrayWithKey($key): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Array key can only be string or integer');
        }
        $instance = new self();
        $instance->targetedArrayKey = $key;

        return $instance;
    }

    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        $value = $builder->getCurrentValue();
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
