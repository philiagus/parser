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
use Philiagus\Parser\Parser\Assert\AssertSame;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AssertSame::class)]
class AssertSameTest extends TestBase
{
    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->filter(fn($value) => $value === $value)
            ->map(fn($value) => [$value, fn($value) => AssertSame::value($value), $value])
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))
            ->map(fn($value) => [$value, fn($value) => AssertSame::value([$value])])
            ->provide(false);
    }

    public static function provideAsFristValueCases(): \Generator
    {
        yield 'same' => [1, 1];
        yield 'same object' => [$o = new \stdClass(), $o];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideAsFristValueCases')]
    public function test_asFirstValue(mixed $value1, mixed $value2): void
    {
        $parser = AssertSame::asFirstValue();
        // just run NAN to test reset
        $parser->parse(Subject::default(NAN));
        try {
            AssertArray::new()
                ->giveEachValue($parser)
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
