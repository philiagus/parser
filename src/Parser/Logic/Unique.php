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
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Result;

final class Unique implements Parser
{

    private array $encounteredValues = [];
    private string $lastRootId = '';

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
        $rootId = $subject->getRootId();
        if ($this->lastRootId !== $rootId) {
            $this->lastRootId = $rootId;
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
