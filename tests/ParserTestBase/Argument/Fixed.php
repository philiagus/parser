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
use Philiagus\Parser\Test\ParserTestBase\ErrorCollection;
use Philiagus\Parser\Util\Debug;

class Fixed implements Argument
{
    private const SUCCESS = 1, ERROR = 2, THROW = 3;

    private array $cases = [];

    public function __construct()
    {
    }

    public function success(mixed $value, ?string $description = null): self
    {
        $description ??= count($this->cases) . ' ' . Debug::stringify($value);
        $this->cases[$description] = [self::SUCCESS, $value];

        return $this;
    }

    public function error(mixed $value, ?string $description = null): self
    {
        $description ??= count($this->cases);
        $this->cases[$description] = [self::ERROR, $value];

        return $this;
    }

    public function generate(mixed $subjectValue, array $generatedArgs, array $successes): Generator
    {
        foreach ($this->cases as $description => [$type, $value]) {
            yield $description => [
                $type === self::SUCCESS,
                function (array $generatedArguments, array $successStack, ErrorCollection $errorCollection = null) use ($type, $value) {
                    if ($type === self::THROW) {
                        $errorCollection?->expectConfigException();
                    }

                    return $value;
                },
            ];
        }
    }

    public function getErrorMeansFail(): bool
    {
        return true;
    }

    public function exception(mixed $value): self
    {
        $this->cases[] = [self::THROW, $value];

        return $this;
    }
}
