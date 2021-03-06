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

namespace Philiagus\Parser\Test\Unit\Exception;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Root;
use PHPUnit\Framework\TestCase;

class ParsingExceptionTest extends TestCase
{
    public function testWithoutPrevious(): void
    {
        $path = new Root('');
        $exception = new ParsingException('value', 'message', $path);
        self::assertSame('message', $exception->getMessage());
        self::assertSame($path, $exception->getPath());
        self::assertNull($exception->getPrevious());
        self::assertSame('value', $exception->getValue());
    }

    public function testWithPrevious(): void
    {
        $previous = new \Exception();
        $path = new Root('');
        $exception = new ParsingException('value', 'message', $path, $previous);
        self::assertSame('message', $exception->getMessage());
        self::assertSame($path, $exception->getPath());
        self::assertSame($previous, $exception->getPrevious());
        self::assertSame('value', $exception->getValue());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function provideAllTypes(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))->provide();
    }

    /**
     * @param $value
     *
     * @dataProvider provideAllTypes
     */
    public function testGetValue($value): void
    {
        $exceptionValue = (new ParsingException($value, 'message', new Root('')))->getValue();
        self::assertTrue(DataProvider::isSame($value, $exceptionValue));
    }
}
