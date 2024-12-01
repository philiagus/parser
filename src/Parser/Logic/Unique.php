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

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Chainable;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\RuntimeParserConfigurationException;
use Philiagus\Parser\Parser\Extract\Append;
use Philiagus\Parser\Result;

/**
 * Creates a parser that acts as a logical gate that will not let the same value through twice.
 *
 * This is best used in combination with an Append parser to have a unique list of elements in the
 * resulting array
 *
 * @package Parser\Logic
 * @see Append
 * @see Chainable::thenAppendTo()
 */
readonly final class Unique implements Parser
{

    private function __construct(
        private Parser        $parser,
        private bool|\Closure $comparison,
    )
    {
    }

    public static function comparingSame(Parser $parser): self
    {
        return new self($parser, true);
    }

    public static function comparingEquals(Parser $parser): self
    {
        return new self($parser, false);
    }

    /**
     * @param \Closure $closure
     * @param Parser $parser
     * @return self
     */
    public static function comparingBy(\Closure $closure, Parser $parser): self
    {
        return new self($parser, $closure);
    }

    #[\Override] public function parse(Subject $subject): Result
    {
        $found = false;
        $value = $subject->getValue();
        $encounteredValues = $subject->getMemory($this, []);
        if ($this->comparison === true) {
            $found = in_array($value, $encounteredValues, true);
        } elseif ($this->comparison === false) {
            foreach ($encounteredValues as $encounteredValue) {
                if ($value == $encounteredValue) {
                    $found = true;
                    break;
                }
            }
        } else {
            try {
                foreach ($encounteredValues as $encounteredValue) {
                    if (($this->comparison)($encounteredValue, $value)) {
                        $found = true;
                        break;
                    }
                }
            } catch (\Throwable $e) {
                throw new RuntimeParserConfigurationException("Comparison closure for Unique threw an exception", $e);
            }
        }

        if (!$found) {
            $encounteredValues[] = $value;
            $subject->setMemory($this, $encounteredValues);
            $this->parser->parse($subject);
        }

        return new Result($subject, $value, []);
    }
}
