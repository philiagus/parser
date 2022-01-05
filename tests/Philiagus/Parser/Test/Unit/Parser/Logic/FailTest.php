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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Logic\Fail;
use PHPUnit\Framework\TestCase;

class FailTest extends TestCase
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
     * @throws \Philiagus\Parser\Exception\ParserConfigurationException
     * @dataProvider provideAnyValue
     */
    public function testFull($value): void
    {
        $parser = Fail::message('message');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('message');
        $parser->parse($value);
    }

}
