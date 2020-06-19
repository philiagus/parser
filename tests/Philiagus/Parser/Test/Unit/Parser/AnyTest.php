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
use Philiagus\Parser\Parser\Any;
use Philiagus\Parser\Test\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class AnyTest extends TestCase
{
    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new Any()) instanceof Parser);
    }

    public function provideAnyValue(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
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
        DataProvider::assertSame($value, Any::new()->parse($value));
    }

}
