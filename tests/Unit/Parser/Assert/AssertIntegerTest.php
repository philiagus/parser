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

namespace Philiagus\Parser\Test\Unit\Parser\Assert;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Parser\Assert\AssertInteger;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AssertInteger::class)]
class AssertIntegerTest extends NumberTestBase
{

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_INTEGER))
            ->map(static fn($value) => [$value, static fn() => AssertInteger::new()])
            ->provide(false);
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INTEGER))
            ->map(static fn($value) => [$value, static fn() => AssertInteger::new(), $value])
            ->provide(false);
    }

    protected static function getSuccessDataProviderUnion(): int
    {
        return DataProvider::TYPE_INTEGER;
    }

    public function testAssertMultipleOf(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => $value, description: 'same value')
                    ->success(fn($value) => intdiv($value, 2), fn($value) => $value / 2 === intdiv($value, 2), description: 'half value')
                    ->success(fn($value) => 1, description: 'value 1')
                    ->error(fn($value) => 0, fn($value) => $value !== 0, description: 'zero'),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($value, array $args, array $successes) => !$successes[0])
                    ->withParameterElement('base', 0)
            )
            ->successProvider(DataProvider::TYPE_INTEGER);
        $builder->run();
    }
}
