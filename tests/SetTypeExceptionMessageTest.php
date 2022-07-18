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

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;
use ReflectionClass;
use ReflectionObject;

trait SetTypeExceptionMessageTest
{
    abstract public function provideInvalidTypesAndParser(): array;

    /**
     * @dataProvider provideInvalidTypesAndParser
     */
    public function testSetTypeExceptionMessageDefaultMessage($invalidValue, \Closure $parser): void
    {
        $parser = $parser($invalidValue);
        $reflection = $this->assertUsesTypeExceptionMessageTrait($parser);

        $method = $reflection->getMethod('getDefaultTypeExceptionMessage');
        $method->setAccessible(true);
        $defaultMessage = $method->invoke($parser);

        self::expectException(ParsingException::class);
        self::expectExceptionMessage(Debug::parseMessage($defaultMessage, ['subject' => $invalidValue]));
        $parser->parse(Subject::default($invalidValue));
    }

    private function assertUsesTypeExceptionMessageTrait(Parser $parser): ReflectionClass
    {
        $class = $reflection = new ReflectionObject($parser);
        do {
            if (in_array(TypeExceptionMessage::class, $class->getTraitNames())) {
                return $reflection;
            }
        } while ($class = $class->getParentClass());

        self::fail('Parser does not provide setTypeExceptionMessage method');

        return $reflection;
    }

    abstract public function expectException(string $exception): void;

    abstract public function expectExceptionMessage(string $exception): void;

    abstract public static function fail(string $message): void;

    /**
     * @dataProvider provideInvalidTypesAndParser
     */
    public function testSetTypeExceptionMessageOverwrittenMessage($invalidValue, \Closure $parser): void
    {
        $parser = $parser($invalidValue);
        $this->assertUsesTypeExceptionMessageTrait($parser);

        $parser->setTypeExceptionMessage('the type is {subject.type}');
        self::expectException(ParsingException::class);
        self::expectExceptionMessage('the type is ' . Debug::getType($invalidValue));
        $parser->parse(Subject::default($invalidValue));
    }

}
