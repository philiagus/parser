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

namespace Philiagus\Parser\Test\Unit\Parser\Extraction;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\Extraction\Assign;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\TestBase;

class AssignTest extends TestBase
{
    use ChainableParserTest;

    public function provideValidValuesAndParsersAndResults(): array
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

    /**
     * @param $value
     *
     * @return void
     * @throws \Philiagus\Parser\Exception\ParsingException
     * @throws \Philiagus\Parser\Exception\RuntimeParserConfigurationException
     * @dataProvider provideAnything
     */
    public function testAssign($value): void
    {
        $something = ['any default value we can think of', $value];
        $parser = Assign::to($something);
        $result = $parser->parse(Subject::default($value));
        self::assertTrue(DataProvider::isSame($value, $something));
        self::assertTrue(DataProvider::isSame($result->getValue(), $value));
    }
}
