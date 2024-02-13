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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Assert\AssertArray;
use Philiagus\Parser\Parser\Assert\AssertEqual;
use Philiagus\Parser\Subject\ArrayKey;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;

/**
 * @covers \Philiagus\Parser\Parser\Assert\AssertEqual
 */
class AssertEqualsTest extends TestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->filter(function ($value) {
                /** @noinspection PhpExpressionWithSameOperandsInspection */
                return $value == $value;
            })
            ->map(
                function ($value) {
                    return [$value, static fn($value) => AssertEqual::value($value), $value];
                }
            )
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->map(
                function ($value) {
                    return [$value, static fn() => AssertEqual::value($value ? false : NAN)];
                }
            )
            ->provide(false);
    }

    public static function provideAsFristValueCases(): \Generator
    {
        yield 'same' => [1, 1];
        yield 'equal' => [1, '1'];
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return void
     * @dataProvider provideAsFristValueCases()
     */
    public function test_asFirstValue(mixed $value1, mixed $value2): void
    {
        $equalParser = AssertEqual::asFirstValue();
        // just run NAN to test reset
        $equalParser->parse(Subject::default(NAN));

        try {
            AssertArray::new()
                ->giveEachValue($equalParser)
                ->parse(
                    Subject::default([$value1, $value2, NAN])
                );
        } catch (ParsingException $e) {
            $arrayKeySubject = $e->getError()->getSubject()->getSubjectChain()[1];
            self::assertInstanceOf(ArrayValue::class, $arrayKeySubject);
            self::assertSame('2', $arrayKeySubject->getDescription());
            return;
        }
        self::fail('No exception thrown');
    }
}
