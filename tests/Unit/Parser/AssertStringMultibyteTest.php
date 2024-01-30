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

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Parser\AssertStringMultibyte;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\AssertStringMultibyte
 */
class AssertStringMultibyteTest extends ParserTestBase
{
    private const ISO_8859_1 = "\xE4";
    private const UTF_8 = "\xC3\xBC";
    private const ASCII = 'u';

    private const VALUE_ENCODING = [
        self::ISO_8859_1 => 'ISO-8859-1',
        self::UTF_8 => 'UTF-8',
        self::ASCII => 'ASCII',
    ];

    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringMultibyte::new(), $value])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringMultibyte::new()])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringMultibyte::new()])
            ->provide(false);
    }

    public function testSetAvailableEncodings(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn() => array_values(self::VALUE_ENCODING))
                    ->success(fn($value) => [self::VALUE_ENCODING[$value]])
                    ->error(fn() => ['ASCII'], fn($value) => $value === self::UTF_8),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($_1, $_2, array $successes) => !$successes[0])
            )
            ->values([self::UTF_8, self::ASCII]);
        $builder->run();
    }

    public function testSetEncoding(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->evaluatedArgument()
                    ->success(fn($value) => self::VALUE_ENCODING[$value])
                    ->error(fn() => 'ASCII', fn($value) => !mb_detect_encoding($value, ['ASCII'], true))
                    ->error(fn() => 'UTF-8', fn($value) => !mb_detect_encoding($value, ['UTF-8'], true)),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn($_1, $_2, array $successes) => !$successes[0])
            )
            ->values([self::UTF_8, self::ISO_8859_1, self::ASCII]);
        $builder->run();
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
                        fn($value) => mb_strlen($value),
                        MetaInformation::class
                    )
            )
            ->values([self::UTF_8, self::ASCII])
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
                    ->success(fn($value) => mb_strlen($value)),
                $builder
                    ->evaluatedArgument()
                    ->success(fn() => null)
                    ->success(fn($value) => 1),
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value, array $args) => mb_substr($value, $args[0], $args[1]),
                        MetaInformation::class
                    )
            )
            ->values([self::UTF_8, self::ASCII])
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
                    ->success(fn($value) => mb_substr($value, 0, 3), fn($value) => $value !== '')
                    ->error(fn($value) => md5($value)),
                $builder
                    ->messageArgument()
                    ->withParameterElement('expected', 0)
                    ->expectedWhen(fn($value, array $_, array $successes) => !$successes[0])
            )
            ->values([self::UTF_8, self::ASCII])
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
                    ->success(fn($value) => mb_substr($value, -3), fn($value) => $value !== '')
                    ->error(fn($value) => md5($value)),
                $builder
                    ->messageArgument()
                    ->withParameterElement('expected', 0)
                    ->expectedWhen(fn($value, array $_, array $successes) => !$successes[0])
            )
            ->values([self::UTF_8, self::ASCII])
            ->successProvider(DataProvider::TYPE_STRING);
        $builder->run();
    }

    public function testGiveEncoding(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->parserArgument()
                    ->expectSingleCall(
                        fn($value) => mb_detect_encoding($value, ['auto'], true),
                        MetaInformation::class
                    )
            )
            ->values([self::UTF_8, self::ASCII])
            ->successProvider(DataProvider::TYPE_STRING);
        $builder->run();
    }

    public function provideSetAvailableEncodingsInvalidEncodings(): array
    {
        return [
            'invalid encoding' => [['UTF-8', 'nope']],
            'not string' => [[true]],
        ];
    }

    /**
     * @param $invalidEncodings
     *
     * @return void
     * @throws ParserConfigurationException
     * @dataProvider provideSetAvailableEncodingsInvalidEncodings
     */
    public function test_setAvailableEncodings_invalidEncoding($invalidEncodings): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertStringMultibyte::new()->setAvailableEncodings($invalidEncodings);
    }

    public function test_setEncoding_invalidEncoding(): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertStringMultibyte::new()->setEncoding('INVALID');
    }

    public function testOfEncoding(): void
    {
        self::assertEquals(
            AssertStringMultibyte::ofEncoding('ASCII', 'MSG'),
            AssertStringMultibyte::new()->setEncoding('ASCII', 'MSG')
        );
    }

    public function testUTF8(): void
    {
        self::assertEquals(
            AssertStringMultibyte::UTF8('MSG'),
            AssertStringMultibyte::ofEncoding('UTF-8', 'MSG')
        );
    }
}
