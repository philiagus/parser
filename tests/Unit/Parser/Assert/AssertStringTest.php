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
use Philiagus\Parser\Parser\Assert\AssertString;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AssertString::class)]
class AssertStringTest extends ParserTestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertString::new(), $value])
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertString::new()])
            ->provide(false);
    }

    public static function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertString::new()])
            ->provide(false);
    }

    public function testGiveLength(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => strlen($value),
                        MetaInformation::class
                    )
            )
            ->successProvider(DataProvider::TYPE_STRING);
        $builder->run();
    }

    public function testGiveSubstring(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn() => 0)
                    ->success(fn($value) => strlen($value)),
                $builder
                    ->evaluatedArgument()
                    ->success(fn() => null)
                    ->success(fn($value) => 1),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value, array $args) => substr($value, $args[0], $args[1]),
                        MetaInformation::class
                    )
            )
            ->successProvider(DataProvider::TYPE_STRING);
        $builder->run();
    }

    public function testAssertStartsWith(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => substr($value, 0, 3), fn($value) => $value !== '')
                    ->error(fn($value) => md5($value)),
                $builder
                    ->messageArgument()
                    ->withParameterElement('expected', 0)
                    ->expectedWhen(fn($value, array $_, array $successes) => !$successes[0])
            )
            ->successProvider(DataProvider::TYPE_STRING);
        $builder->run();
    }

    public function testAssertEndsWith(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => substr($value, -3), fn($value) => $value !== '')
                    ->error(fn($value) => md5($value)),
                $builder
                    ->messageArgument()
                    ->withParameterElement('expected', 0)
                    ->expectedWhen(fn($value, array $_, array $successes) => !$successes[0])
            )
            ->successProvider(DataProvider::TYPE_STRING);
        $builder->run();
    }

    public function testAssertRegex(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->configException(fn() => 'not a pattern')
                    ->success(fn($value) => $value !== '' ? '~^.++$~' : '~^$~')
                    ->error(fn($value) => $value === '' ? '~^.++$~' : '~^$~'),
                $builder
                    ->messageArgument()
                    ->withParameterElement('pattern', 0)
                    ->expectedWhen(fn($value, array $_, array $successes) => !$successes[0])
            )
            ->successProvider(DataProvider::TYPE_STRING);

        $builder->run();
    }


}
