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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Logic\Fail;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Util\Debug;
use PHPUnit\Framework\TestCase;

class FailTest extends ParserTestBase
{

    public function provideAnyValue(): array
    {
        return (new DataProvider())->provide();
    }

    /**
     * @param $value
     *
     * @return void
     * @throws ParsingException
     * @throws ParserConfigurationException
     * @dataProvider provideAnyValue
     */
    public function testFull($value): void
    {
        $parser = Fail::message('message {subject.debug}');
        $expectedMessage = Debug::parseMessage('message {subject.debug}', ['subject' => $value]);
        $result = $parser->parse(Subject::default($value, false));
        self::assertFalse($result->isSuccess());
        self::assertCount(1, $result->getErrors());
        self::assertSame($result->getErrors()[0]->getMessage(), $expectedMessage);

        self::expectException(ParsingException::class);
        self::expectExceptionMessage($expectedMessage);
        $parser->parse(Subject::default($value));
    }

}
