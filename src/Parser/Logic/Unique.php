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

use Philiagus\Parser\Contract\Chainable;
use Philiagus\Parser\Contract\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Result;
use Philiagus\Parser\Parser\Extract\Append;

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
final class Unique implements Parser
{

    private array $encounteredValues = [];
    private ?Subject $currentRoot = null;

    private function __construct(
        private readonly Parser $parser,
        private readonly bool   $same,
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

    #[\Override] public function parse(Subject $subject): Result
    {
        $root = $subject->getRoot();
        if ($this->currentRoot !== $root) {
            $this->currentRoot = $root;
            $this->encounteredValues = [];
        }

        $value = $subject->getValue();
        if ($this->same) {
            $found = in_array($value, $this->encounteredValues, true);
        } else {
            $found = false;
            foreach ($this->encounteredValues as $encounteredValue) {
                if ($value == $encounteredValue) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $this->encounteredValues[] = $value;
            $this->parser->parse($subject);
        }

        return new \Philiagus\Parser\Result($subject, $value, []);
    }
}
