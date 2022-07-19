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

class Fixed implements Argument
{

    private array $cases = [];

    public function __construct()
    {
    }

    public function success(mixed $value, ?string $description = null): self
    {
        $description ??= count($this->cases);
        $this->cases[$description] = [true, $value];
        return $this;
    }

    public function error(mixed $value, ?string $description = null): self
    {
        $description ??= count($this->cases);
        $this->cases[$description] = [false, $value];
        return $this;
    }

    public function generate(mixed $subjectValue, array $generatedArgs): Generator
    {
        foreach($this->cases as $description => [$success, $value]) {
            yield $description => [$success, fn() => $value];
        }
    }
}
