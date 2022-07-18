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
use Philiagus\Parser\Parser\ConvertToString;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\Mock\ParserMock;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;

class ConvertToStringTest extends TestBase
{
    use SetTypeExceptionMessageTest,
        ValidValueParserTest,
        InvalidValueParserTest,
        ChainableParserTest;

    public function provideInvalidValuesAndParsers(): array
    {
        return [
            'array' => [[1, 2, 3], fn() => ConvertToString::new()],
            '+inf' => [INF, fn() => ConvertToString::new()],
            '-inf' => [-INF, fn() => ConvertToString::new()],
            'nan' => [NAN, fn() => ConvertToString::new()],
            'non __toString object' => [(object) [], fn() => ConvertToString::new()],
            'null' => [null, fn() => ConvertToString::new()],
            'true' => [true, fn() => ConvertToString::new()],
            'false' => [false, fn() => ConvertToString::new()],
            'non string array element with conversion' => [[1, 2, 3], fn() => ConvertToString::new()->setImplodeOfArrays(',')],
            'array converter resulted in non string' => [
                ['yes'],
                fn() => ConvertToString::new()
                    ->setImplodeOfArrays(',', $this->prophesizeParser([['yes', false]])),
            ],
            'array converter resulted in exception' => [
                ['yes'],
                fn() => ConvertToString::new()
                    ->setImplodeOfArrays(',', (new ParserMock())->error()),
            ],
        ];
    }

    public function provideInvalidTypesAndParser(): array
    {
        return [
            [INF, fn() => ConvertToString::new()],
        ];
    }

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_INTEGER | DataProvider::TYPE_FLOAT | DataProvider::TYPE_STRING))
            ->map(
                fn($value) => [$value, fn() => ConvertToString::new(), (string) $value]
            )
            ->addCase('null', [null, fn() => ConvertToString::new()->setNullValue('NuLl'), 'NuLl'])
            ->addCase(
                'tostring object',
                [
                    new class() {
                        public function __toString()
                        {
                            return 'oi';
                        }
                    },
                    fn() => ConvertToString::new(),
                    'oi',
                ]
            )
            ->addCase('bool: true', [true, fn() => ConvertToString::new()->setBooleanValues('yes', 'no'), 'yes'])
            ->addCase('bool: false', [false, fn() => ConvertToString::new()->setBooleanValues('yes', 'no'), 'no'])
            ->addCase('array: implode', [['a', 'b', 'c'], fn() => ConvertToString::new()->setImplodeOfArrays(':'), 'a:b:c'])
            ->addCase(
                'array: implode with conversion',
                [
                    [true, false],
                    function () {
                        return ConvertToString::new()
                            ->setImplodeOfArrays(
                                '_',
                                $this->prophesizeParser(
                                    [
                                        [true, 'yep'],
                                        [false, 'nope'],
                                    ]
                                )
                            );
                    },
                    'yep_nope',
                ]
            )
            ->provide(false);
    }
}
