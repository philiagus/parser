<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\AssertStringRegex;
use Philiagus\Parser\Path\MetaInformation;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AssertStringRegexTest extends TestCase
{
    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new AssertStringRegex()) instanceof Parser);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideValidValues(): array
    {
        return DataProvider::provide(DataProvider::TYPE_STRING);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideInvalidValues(): array
    {
        return DataProvider::provide((int) ~DataProvider::TYPE_STRING);
    }

    /**
     * @param $value
     *
     * @dataProvider provideValidValues
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testThatItAcceptsString($value): void
    {
        self::assertSame($value, (new AssertStringRegex())->setPattern('//')->parse($value));
    }

    /**
     * @param $value
     *
     * @dataProvider  provideInvalidValues
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testThatItBlocksNonString($value): void
    {
        $this->expectException(ParsingException::class);
        (new AssertStringRegex())->setPattern('//')->parse($value);
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testOverwriteTypeExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage($msg);
        (new AssertStringRegex())->setPattern('//')->overwriteTypeExceptionMessage($msg)->parse(false);
    }

    /**
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    public function testOverwriteTypeExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(' boolean boolean FALSE');
        (new AssertStringRegex())->setPattern('//')->overwriteTypeExceptionMessage('{value} {value.type} {value.debug}')->parse(false);
    }

    public function testSetPatternSuccess(): void
    {
        $parser = new AssertStringRegex();
        self::assertSame('a', $parser->setPattern('/./')->parse('a'));
    }

    public function testStaticPattern(): void
    {
        self::assertSame('a', AssertStringRegex::pattern('/./')->parse('a'));
    }

    public function testSetPatternInvalidPatternException(): void
    {
        $parser = new AssertStringRegex();
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('An invalid regular expression was provided');
        $parser->setPattern('');
    }

    public function testSetPatternCanOnlyBeCalledOnce(): void
    {

        $parser = new AssertStringRegex();
        $parser->setPattern('//');
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('The pattern for AssertStringRegex has already been defined and cannot be overwritten');
        $parser->setPattern('//');
    }

    public function testStaticPatternInvalidPatternException(): void
    {
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('An invalid regular expression was provided');
        AssertStringRegex::pattern('');
    }

    public function testStaticPatternSetPatternCanOnlyBeCalledOnce(): void
    {
        $parser = AssertStringRegex::pattern('//');
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('The pattern for AssertStringRegex has already been defined and cannot be overwritten');
        $parser->setPattern('//');
    }

    public function testSetPatternCustomExceptionMessageReplacers(): void
    {
        $parser = new AssertStringRegex();
        $parser->setPattern('/a/', '{value} {value.type} {value.debug} | {pattern} {pattern.debug}');
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('b string string<ASCII>(1)"b" | /a/ string<ASCII>(3)"/a/"');
        $parser->parse('b');
    }

    public function testWithMatches(): void
    {
        $parser = AssertStringRegex::pattern('/(?<a>a)b(?<c>c)/');
        for ($i = 0; $i < 10; $i++) {
            $child = $this->prophesize(ParserContract::class);
            $child->parse(['abc', 'a' => 'a', 'a', 'c' => 'c', 'c'], Argument::type(MetaInformation::class))->shouldBeCalledOnce();
            $parser->withMatches($child->reveal());
        }
        $parser->parse('abc');
    }

    public function testSetGlobalCanOnlyBeCalledOnce(): void
    {
        $parser = AssertStringRegex::pattern('//')->setGlobal(false);
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('Global matching configuration of AssertStringRegex has already been defined and cannot be overwritten');
        $parser->setGlobal(false);
    }

    public function testSetOffsetCanOnlyBeCalledOnce(): void
    {
        $parser = AssertStringRegex::pattern('//')->setOffset(1);
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('Offset configuration of AssertStringRegex has already been defined and cannot be overwritten');
        $parser->setOffset(1);
    }

    public function testSetOffsetCaptureCanOnlyBeCalledOnce(): void
    {
        $parser = AssertStringRegex::pattern('//')->setOffsetCapture(true);
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage(
            'Offset capture configuration of AssertStringRegex has already been defined and cannot be overwritten'
        );
        $parser->setOffsetCapture(true);
    }

    public function testSetUnmatchedAsNullCanOnlyBeCalledOnce(): void
    {
        $parser = AssertStringRegex::pattern('//')->setUnmatchedAsNull(true);
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage(
            'Unmatched as null configuration of AssertStringRegex has already been defined and cannot be overwritten'
        );
        $parser->setUnmatchedAsNull(true);
    }

    public function provideValidGlobalValues(): array
    {
        return [
            'false' => [false],
            'true' => [true],
            'PREG_PATTERN_ORDER' => [PREG_PATTERN_ORDER],
            'PREG_SET_ORDER' => [PREG_SET_ORDER],
        ];
    }

    public function provideInvalidGlobalValues(): array
    {
        $values = DataProvider::provide((int) ~(DataProvider::TYPE_INTEGER | DataProvider::TYPE_BOOLEAN));
        $values[PREG_OFFSET_CAPTURE] = [PREG_OFFSET_CAPTURE];

        return $values;
    }

    /**
     * @param $validValue
     *
     * @dataProvider provideValidGlobalValues
     * @throws ParserConfigurationException|ParsingException
     */
    public function testSetGlobalAcceptsValidValues($validValue): void
    {
        self::assertSame('a',
            AssertStringRegex::pattern('/a/')
                ->setGlobal($validValue)
                ->parse('a')
        );
    }

    /**
     * @param $invalidValue
     *
     * @dataProvider  provideInvalidGlobalValues
     */
    public function testSetGlobalBlocksInvalidValues($invalidValue): void
    {
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage(
            'Global matching configuration of AssertStringRegex must be provided as bool, PREG_SET_ORDER or PREG_PATTERN_ORDER'
        );
        AssertStringRegex::pattern('/a/')
            ->setGlobal($invalidValue);
    }

    public function provideMatchingCases(): array
    {
        $cases = [];
        $cases['basic'] = [
            'subject' => 'a',
            'pattern' => '/a/',
            'results' => [
                false => ['a'],
                PREG_PATTERN_ORDER => [['a']],
                PREG_SET_ORDER => [['a']],
            ],
        ];

        $cases['offset'] = [
            'subject' => 'abc',
            'pattern' => '/.+/',
            'offset' => 1,
            'results' => [
                false => ['bc'],
                PREG_PATTERN_ORDER => [['bc']],
                PREG_SET_ORDER => [['bc']],
            ],
        ];

        $cases['match groups'] = [
            'subject' => 'a1b1c1',
            'pattern' => '/(?<g>.)1/',
            'results' => [
                false => ['a1', 'g' => 'a', 'a'],
                PREG_PATTERN_ORDER => [['a1', 'b1', 'c1'], 'g' => ['a', 'b', 'c'], ['a', 'b', 'c']],
                PREG_SET_ORDER => [['a1', 'g' => 'a', 'a'], ['b1', 'g' => 'b', 'b'], ['c1', 'g' => 'c', 'c']],
            ],
        ];

        $cases['match offset'] = [
            'subject' => 'abc',
            'pattern' => '/./',
            'offsetCapture' => true,
            'results' => [
                false => [[0, 'a']],
                PREG_PATTERN_ORDER => [
                    [
                        ['a', 0],
                        ['b', 1],
                        ['c', 2],
                    ],
                ],
                PREG_SET_ORDER => [
                    [['a', 0]],
                    [['b', 1]],
                    [['c', 2]],
                ],
            ],
        ];

        $cases['match unmatchedAsNull'] = [
            'subject' => 'abc',
            'pattern' => '/(a)(x)?(b)(c)/',
            'unmatchedAsNull' => true,
            'results' => [
                false => ['abc', 'a', null, 'b', 'c'],
                PREG_PATTERN_ORDER => [['abc'], ['a'], [null], ['b'], ['c']],
                PREG_SET_ORDER => [['abc', 'a', null, 'b', 'c']],
            ],
        ];

        $cases['match unmatchedAsNull, mach offset and offset'] = [
            'subject' => 'abc',
            'pattern' => '/(x)?(b)(c)/',
            'unmatchedAsNull' => true,
            'offset' => 1,
            'offsetCapture' => true,
            'results' => [
                false => [["bc", 1], [null, -1], ["b", 1], ["c", 2]],
                PREG_PATTERN_ORDER => [[["bc", 1]], [[null, -1]], [["b", 1]], [["c", 2]]],
                PREG_SET_ORDER => [[["bc", 1], [null, -1], ["b", 1], ["c", 2]]],
            ],
        ];

        $result = [];
        foreach ($cases as $name => $case) {
            $case += [
                'offset' => 0,
                'offsetCapture' => false,
                'unmatchedAsNull' => false,
            ];
            if (isset($case['result'][PREG_PATTERN_ORDER])) {
                $case['result'][true] = $case['result'][PREG_PATTERN_ORDER];
            }
            foreach ($case['results'] as $global => $excpected) {
                $fullName = $name . ' ';
                switch ($global) {
                    case 0:
                        $fullName .= 'single';
                        $global = false;
                        break;
                    case 1:
                        $fullName .= 'group';
                        $global = true;
                        break;
                    case PREG_PATTERN_ORDER:
                        $fullName .= 'PREG_PATTERN_ORDER';
                        $global = PREG_PATTERN_ORDER;
                        break;
                    case PREG_SET_ORDER:
                        $fullName .= 'PREG_SET_ORDER';
                        $global = PREG_SET_ORDER;
                        break;
                    default:
                        throw new \LogicException("Could not generate cases");
                }
                $result[$fullName] = [
                    $case['subject'],
                    $case['pattern'],
                    $global,
                    $case['offsetCapture'],
                    $case['unmatchedAsNull'],
                    $case['offset'],
                    $excpected,
                ];
            }
        }

        return $result;
    }

    /**
     * @param string $subject
     * @param string $pattern
     * @param $global
     * @param bool $offsetCapture
     * @param bool $unmatchedAsNull
     * @param int $offset
     * @param array $expectedArray
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideMatchingCases
     */
    public function testMatching(
        string $subject,
        string $pattern,
        $global,
        bool $offsetCapture,
        bool $unmatchedAsNull,
        int $offset,
        array $expectedArray
    ): void
    {
        $child = $this->prophesize(ParserContract::class);
        $child->parse($expectedArray, Argument::type(MetaInformation::class))->shouldBeCalledOnce();
        AssertStringRegex::pattern($pattern)
            ->setGlobal($global)
            ->setOffsetCapture($offsetCapture)
            ->setUnmatchedAsNull($unmatchedAsNull)
            ->setOffset($offset)
            ->withMatches($child->reveal())
            ->parse($subject);
    }

    public function testExceptionIfNoPatternSet(): void
    {
        $parser = new AssertStringRegex();
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage('Called AssertStringRegex without a pattern to match against');
        $parser->parse('');
    }

}
