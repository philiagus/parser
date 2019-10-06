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

namespace Philiagus\Test\Parser\Unit\Base;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Type\AcceptsMixed;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ParserTest extends TestCase
{

    public const ANOTHER_VALUE = 'another value';

    public function allValuesProvider(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    /**
     * @dataProvider allValuesProvider
     *
     * @param $value
     */
    public function testThatItWritesByReferenceAndReturnsValue($value): void
    {
        $target = null;
        $parser = new class($target) extends Parser
        {

            private $wasCalled = false;

            public function wasCalled(): bool
            {
                return $this->wasCalled;
            }

            protected function execute($value, Path $path)
            {
                $this->wasCalled = true;

                // make an array out of it so that we can test if it really was used
                return [$value, ParserTest::ANOTHER_VALUE];
            }
        };

        $result = $parser->parse($value);

        self::assertTrue($parser->wasCalled());
        if (is_float($value) && is_nan($value)) {
            // assert reference target
            self::assertTrue(is_array($target));
            self::assertSame([0, 1], array_keys($target));
            self::assertTrue(is_nan($target[0]));
            self::assertSame(ParserTest::ANOTHER_VALUE, $target[1]);

            // assert result
            self::assertTrue(is_array($result));
            self::assertSame([0, 1], array_keys($result));
            self::assertTrue(is_nan($result[0]));
            self::assertSame(ParserTest::ANOTHER_VALUE, $result[1]);
        } else {
            self::assertSame([$value, ParserTest::ANOTHER_VALUE], $target);
            self::assertSame([$value, ParserTest::ANOTHER_VALUE], $result);
            self::assertSame($target, $result);
        }
    }

}