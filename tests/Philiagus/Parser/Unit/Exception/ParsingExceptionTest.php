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

namespace Philiagus\Test\Parser\Unit\Exception;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class ParsingExceptionTest extends TestCase
{

    public function provideCases(): array
    {
        return [
            'no previous' => [
                Parser::PATH_SEPARATOR . 'start' . Parser::PATH_SEPARATOR . 'middle' . Parser::PATH_SEPARATOR . 'end',
                ['start', 'middle', 'end'],
                null,
            ],
            'with previous' => [
                '',
                [],
                new \Exception(),
            ],
            'only cuts first path separator' => [
                Parser::PATH_SEPARATOR . Parser::PATH_SEPARATOR . 'start' . Parser::PATH_SEPARATOR . 'middle' . Parser::PATH_SEPARATOR . 'end',
                ['', 'start', 'middle', 'end'],
                null,
            ],
        ];
    }


    /**
     * @dataProvider  provideCases
     *
     * @param string $path
     * @param array $expectedPath
     * @param \Throwable|null $previous
     */
    public function testClass(string $path, array $expectedPath, ?\Throwable $previous): void
    {
        $exception = new ParsingException('value', 'message', $path, $previous);
        self::assertSame('message', $exception->getMessage());
        self::assertSame($expectedPath, $exception->getPath());
        self::assertSame($previous, $exception->getPrevious());
        self::assertSame('value', $exception->getValue());
    }


    public function provideAllTypes(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    /**
     * @param $value
     * @dataProvider provideAllTypes
     */
    public function testGetValue($value): void
    {
        $exceptionValue = (new ParsingException($value, 'message', ''))->getValue();
        if(is_float($value) && is_nan($value)) {
            self::assertNan($exceptionValue);
        } else {
            self::assertSame($value, $exceptionValue);
        }
    }
}
