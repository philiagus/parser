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

namespace Philiagus\Parser\Test\Unit\Parser\Logic;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Logic\Fail;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Util\Stringify;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Fail::class)]
class FailTest extends ParserTestBase
{

    public static function provideAnyValue(): array
    {
        return (new DataProvider())->provide();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideAnyValue')]
    public function testFull($value): void
    {
        $parser = Fail::message('message {value.debug}');
        $expectedMessage = Stringify::parseMessage('message {value.debug}', ['value' => $value]);
        $result = $parser->parse(Subject::default($value, throwOnError: false));
        self::assertFalse($result->isSuccess());
        self::assertCount(1, $result->getErrors());
        self::assertSame($result->getErrors()[0]->getMessage(), $expectedMessage);

        self::expectException(ParsingException::class);
        self::expectExceptionMessage($expectedMessage);
        $parser->parse(Subject::default($value));
    }

}
