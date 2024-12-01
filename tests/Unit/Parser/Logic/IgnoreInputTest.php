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

namespace Philiagus\Parser\Test\Unit\Parser\Logic;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\Logic\IgnoreInput;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IgnoreInput::class)]
class IgnoreInputTest extends TestBase
{
    use ChainableParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(fn($value) => [$value, fn($value) => IgnoreInput::resultIn(!$value), !$value])
            ->provide(false);
    }

    public static function provideAnyValue(): array
    {
        return (new DataProvider())->provide();
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('provideAnyValue')]
    public function testFull($anything): void
    {
        $obj = new \stdClass();
        self::assertSame(
            $obj,
            IgnoreInput::resultIn($obj)->parse(Subject::default($anything))->getValue()
        );
    }
}
