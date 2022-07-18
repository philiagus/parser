<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\ParserTestBase\Argument;

use Generator;
use Philiagus\Parser\Test\ParserTestBase\Argument;
use Philiagus\Parser\Util\Debug;

class Evaluated implements Argument
{

    private array $cases = [];

    public function success(\Closure $generator, \Closure $eligible = null, string $description = null): self
    {
        $description ??= count($this->cases);
        $this->cases[$description] = [
            'success' => true,
            'generator' => $generator,
            'eligible' => $eligible ?? fn() => true,
        ];

        return $this;
    }

    public function error(\Closure $generator, \Closure $eligible = null, string $description = null): self
    {
        $description ??= count($this->cases);
        $this->cases[$description] = [
            'success' => false,
            'generator' => $generator,
            'eligible' => $eligible ?? fn() => true,
        ];

        return $this;
    }

    public function generate(mixed $subjectValue): Generator
    {
        foreach ($this->cases as $name => ['success' => $success, 'generator' => $generator, 'eligible' => $eligible]) {
            if ($eligible($subjectValue)) {
                $value = $generator($subjectValue);
                $name = '#' . $name . ' ' . Debug::stringify($value);
                yield $name => [$success, $value];
            }
        }
    }

}
