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
use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Test\ParserTestBase\Argument;

class Generated implements Argument
{

    public function __construct(private readonly int $flags)
    {

    }

    public function generate(mixed $subjectValue, array $generatedArgs, array $successes): Generator
    {
        $provider = new DataProvider($this->flags);
        foreach ($provider->provide(false) as $name => $value) {
            yield $name => [true, $value];
        }
    }

    public function getErrorMeansFail(): bool
    {
        return true;
    }
}
