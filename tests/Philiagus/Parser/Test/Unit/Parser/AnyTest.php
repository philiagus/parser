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

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Parser\Any;
use PHPUnit\Framework\TestCase;

class AnyTest extends TestCase
{
    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new Any()) instanceof Parser);
    }

    public function provideAnyValue(): array
    {
        return (new DataProvider())->provide();
    }

    /**
     * @param $value
     *
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @throws \Philiagus\Parser\Exception\ParsingException
     * @dataProvider provideAnyValue
     */
    public function testItAcceptsAnyValue($value): void
    {
        self::assertTrue(DataProvider::isSame($value, Any::new()->parse($value)));
    }

}
