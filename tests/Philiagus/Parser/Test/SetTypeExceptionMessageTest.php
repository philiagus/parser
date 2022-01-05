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

namespace Philiagus\Parser\Test;

use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

trait SetTypeExceptionMessageTest
{
    use InvalidValueParserTest;

    abstract public function expectException(string $exception): void;
    abstract public function expectExceptionMessage(string $exception): void;
    abstract public function fail(string $message): void;


    /**
     * @dataProvider provideInvalidValuesAndParsers
     */
    public function testSetTypeExceptionMessage($invalidValue, Parser $parser): void
    {
        if(!method_exists($parser, 'setTypeExceptionMessage')) {
            self::fail('Parser does not provide setTypeExceptionMessage method');
        }
        $parser->setTypeExceptionMessage('the type is {value.type}');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('the type is ' . Debug::getType($invalidValue));
        $parser->parse($invalidValue);
    }

}
