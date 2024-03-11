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

namespace Philiagus\Parser\Test\Unit\Parser\Extract;

use ArrayAccess;
use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Exception\RuntimeParserConfigurationException;
use Philiagus\Parser\Parser\Extract\Append;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Prophecy\Argument;

#[CoversClass(Append::class)]
class AppendTest extends TestBase
{
    use ValidValueParserTestTrait, ChainableParserTestTrait;

    #[\PHPUnit\Framework\Attributes\DataProvider('provideAnything')]
    public function testAppendsToAnything($value): void
    {
        $parser = Append::to($unsetTarget);
        $parser->parse(Subject::default($value));
        $parser->parse(Subject::default($value));
        self::assertTrue(DataProvider::isSame([$value, $value], $unsetTarget));

        $presetNull = null;
        $parser = Append::to($presetNull);
        $parser->parse(Subject::default($value));
        $parser->parse(Subject::default($value));
        self::assertTrue(DataProvider::isSame([$value, $value], $presetNull));

        $presetArray = ['first element'];
        $parser = Append::to($presetArray);
        $parser->parse(Subject::default($value));
        $parser->parse(Subject::default($value));
        self::assertTrue(DataProvider::isSame(['first element', $value, $value], $presetArray));

        $presetArrayAccess = $this->prophesize(ArrayAccess::class);
        $presetArrayAccess
            ->offsetSet(null, Argument::that(static fn($arg) => DataProvider::isSame($value, $arg)))
            ->shouldBeCalledTimes(2);
        $presetArrayAccess = $presetArrayAccess->reveal();
        $parser = Append::to($presetArrayAccess);
        $parser->parse(Subject::default($value));
        $parser->parse(Subject::default($value));
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(
                fn($value) => [
                    $value,
                    function () {
                        $target = null;

                        return Append::to($target);
                    },
                    $value,
                ]
            )
            ->provide(false);
    }
}
