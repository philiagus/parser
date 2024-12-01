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

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\Extract\Assign;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\TestBase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Assign::class)]
class AssignTest extends TestBase
{
    use ChainableParserTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(
                fn($value) => [
                    $value,
                    function () {
                        $target = null;

                        return Assign::to($target);
                    },
                    $value,
                ]
            )
            ->provide(false);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideAnything')]
    public function testAssign($value): void
    {
        $something = ['any default value we can think of', $value];
        $parser = Assign::to($something);
        $result = $parser->parse(Subject::default($value));
        self::assertTrue(DataProvider::isSame($value, $something));
        self::assertTrue(DataProvider::isSame($result->getValue(), $value));
    }
}
