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

use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;
use ReflectionClass;
use ReflectionObject;

trait OverwritableTypeErrorMessageTest
{
    abstract public function provideInvalidTypesAndParser(): array;

    /**
     * @dataProvider provideInvalidTypesAndParser
     */
    public function testSetTypeErrorMessageDefaultMessage($invalidValue, \Closure $parser): void
    {
        $parser = $parser($invalidValue);
        $reflection = $this->assertUsesTypeErrorMessageTrait($parser);

        $method = $reflection->getMethod('getDefaultTypeErrorMessage');
        $defaultMessage = $method->invoke($parser);

        self::expectException(ParsingException::class);
        self::expectExceptionMessage(Debug::parseMessage($defaultMessage, ['subject' => $invalidValue]));
        $parser->parse(Subject::default($invalidValue));
    }

    private function assertUsesTypeErrorMessageTrait(Parser $parser): ReflectionClass
    {
        $class = $reflection = new ReflectionObject($parser);
        do {
            if (in_array(OverwritableTypeErrorMessage::class, $class->getTraitNames())) {
                return $reflection;
            }
        } while ($class = $class->getParentClass());

        self::fail('Parser does not provide setTypeErrorMessage method');

        return $reflection;
    }

    abstract public function expectException(string $exception): void;

    abstract public function expectExceptionMessage(string $exception): void;

    abstract public static function fail(string $message): void;

    /**
     * @dataProvider provideInvalidTypesAndParser
     */
    public function testSetTypeErrorMessageOverwrittenMessage($invalidValue, \Closure $parser): void
    {
        $parser = $parser($invalidValue);
        $this->assertUsesTypeErrorMessageTrait($parser);

        $parser->setTypeErrorMessage('the type is {subject.type}');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('the type is ' . Debug::getType($invalidValue));
        $parser->parse(Subject::default($invalidValue));
    }

}
