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
use Philiagus\Parser\Util\Stringify;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionObject;

trait OverwritableTypeErrorMessageTestTrait
{
    abstract public static function provideInvalidTypesAndParser(): array;

    #[DataProvider('provideInvalidTypesAndParser')]
    public function testSetTypeErrorMessageDefaultMessage($invalidValue, \Closure $parser): void
    {
        $parser = $parser($invalidValue);
        /** @var Parser $parser */
        $reflection = $this->assertUsesTypeErrorMessageTrait($parser);

        $method = $reflection->getMethod('getDefaultTypeErrorMessage');
        $defaultMessage = $method->invoke($parser);

        $result = $parser->parse(Subject::default($invalidValue, throwOnError: false));
        self::assertTrue($result->hasErrors());
        self::assertCount(1, $result->getErrors());

        self::expectException(ParsingException::class);
        self::expectExceptionMessage(Stringify::parseMessage($defaultMessage, ['value' => $invalidValue]));
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
    }

    abstract public static function fail(string $message): never;

    abstract public function expectException(string $exception): void;

    abstract public function expectExceptionMessage(string $exception): void;

    #[DataProvider('provideInvalidTypesAndParser')]
    public function testSetTypeErrorMessageOverwrittenMessage($invalidValue, \Closure $parser): void
    {
        $parser = $parser($invalidValue);
        /** @var Parser $parser */
        $this->assertUsesTypeErrorMessageTrait($parser);

        $parser->setTypeErrorMessage('the type is {value.type}');

        $result = $parser->parse(Subject::default($invalidValue, throwOnError: false));
        self::assertTrue($result->hasErrors());
        self::assertCount(1, $result->getErrors());

        self::expectException(ParsingException::class);
        self::expectExceptionMessage('the type is ' . Stringify::getType($invalidValue));
        $parser->parse(Subject::default($invalidValue));
    }

}
