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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Exception\RuntimeParserConfigurationException;
use Philiagus\Parser\Parser\Assert\AssertStringRegex;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AssertStringRegex::class)]
class AssertStringRegexTest extends ParserTestBase
{

    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringRegex::pattern('/.?/'), $value])
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringRegex::pattern('/.?/')])
            ->provide(false);
    }

    public static function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => AssertStringRegex::pattern('/.?/')])
            ->provide(false);
    }

    public function testExceptionOnInvalidPattern(): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertStringRegex::pattern('not a pattern');
    }

    public static function provideCaptureVariants(): array
    {
        $cases = [];
        foreach ([
                     'simple pattern' => '/i/',
                     'unicode pattern' => '/ü/u',
                     'capture pattern' => '/(?<char>.)/u',
                     'capture pattern unicode' => '/(?<char>..)/u',
                     'unmatched' => '/(?<un>ö)?/u',
                 ] as $patternName => $pattern) {
            foreach ([
                         'global unset' => null,
                         'global true' => true,
                         'global false' => false,
                         'global PREG_PATTERN_ORDER' => PREG_PATTERN_ORDER,
                         'global PREG_SET_ORDER' => PREG_SET_ORDER,
                     ] as $globalName => $global) {
                foreach ([
                             'no offset' => null,
                             'offset 0' => 0,
                             'offset 10' => 10,
                         ] as $offsetName => $offset) {
                    foreach ([
                                 'offset capture not given' => null,
                                 'offset capture true' => true,
                                 'offset capture false' => false,
                             ] as $offsetCaptureName => $offsetCapture) {
                        foreach ([
                                     'unmatched null not given' => null,
                                     'unmatched null true' => true,
                                     'unmatched null false' => false,
                                 ] as $unmatchedName => $unmatchedNull) {
                            $cases["$patternName | $globalName | $offsetName | $offsetCaptureName | $unmatchedName"] = [
                                $pattern, $global, $offset, $offsetCapture, $unmatchedNull, 'this is a very long string with multiple things and ü',
                            ];
                        }
                    }
                }
            }
        }

        return $cases;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCaptureVariants')]
    public function testCaptureVariants(
        string $pattern,
               $global,
        ?int   $offset,
        ?bool  $offsetCapture,
        ?bool  $unmatchedNull,
        string $subject
    ): void
    {
        $method = 'preg_match';
        $parser = AssertStringRegex::pattern($pattern);
        $flags = 0;
        if ($global !== null) {
            $parser->setGlobal($global);
            if ($global) {
                $method = 'preg_match_all';
                $flags |= $global === true ? PREG_PATTERN_ORDER : $global;
            }
        }
        if ($offset !== null) {
            $parser->setOffset($offset);
        }
        if ($offsetCapture !== null) {
            $parser->setOffsetCapture($offsetCapture);
            if ($offsetCapture) $flags |= PREG_OFFSET_CAPTURE;
        }
        if ($unmatchedNull !== null) {
            $parser->setUnmatchedAsNull($unmatchedNull);
            if ($unmatchedNull) $flags |= PREG_UNMATCHED_AS_NULL;
        }
        $matches = [];
        $result = $method($pattern, $subject, $matches, $flags, $offset ?? 0);
        $parser
            ->giveMatches($this->prophesizeParser([
                [$matches, $matches],
            ]))
            ->giveNumberOfMatches($this->prophesizeParser([$result]))
            ->parse(Subject::default($subject));
    }


    public function test_setGlobal_invalid(): void
    {
        self::expectException(ParserConfigurationException::class);
        AssertStringRegex::pattern('//')
            ->setGlobal(984651651);
    }

    public function testNotMatch(): void
    {
        self::expectException(ParsingException::class);
        AssertStringRegex::pattern('/u/')
            ->parse(Subject::default('f'));
    }

    public function testNotMatchNoThrow(): void
    {
        $result = AssertStringRegex::pattern('/u/')
            ->parse(Subject::default('f', throwOnError: false));
        self::assertFalse($result->isSuccess());
        self::assertCount(1, $result->getErrors());
    }
}
