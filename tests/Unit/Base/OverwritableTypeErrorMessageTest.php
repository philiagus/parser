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

namespace Philiagus\Parser\Test\Unit\Base;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Stringify;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class OverwritableTypeErrorMessageTest extends TestCase
{
    public static function provideCases(): \Generator
    {
        foreach ((new DataProvider())->provide(false) as $name => $value) {
            yield "Default Message No Builder -> $name" => [
                $value,
                self::createParserWithoutBuilder(...),
                Stringify::parseMessage('DEFAULT {value.debug}', ['value' => $value]),
            ];
            yield "Custom Message No Builder -> $name" => [
                $value,
                fn() => self::createParserWithoutBuilder()->setTypeErrorMessage('CUSTOM {value.debug}'),
                Stringify::parseMessage('CUSTOM {value.debug}', ['value' => $value]),
            ];
            yield "Default Message Builder -> $name" => [
                $value,
                self::createParserWithBuilder(...),
                Stringify::parseMessage('DEFAULT {value.debug}', ['value' => $value]),
            ];
            yield "Custom Message Builder -> $name" => [
                $value,
                fn() => self::createParserWithBuilder()->setTypeErrorMessage('CUSTOM {value.debug}'),
                Stringify::parseMessage('CUSTOM {value.debug}', ['value' => $value]),
            ];
        }
    }

    private
    static function createParserWithoutBuilder(): Parser
    {
        return new class() implements Parser {
            use OverwritableTypeErrorMessage;

            public function parse(Subject $subject): never
            {
                /** @var Error $error */
                $error = $this->getTypeError($subject);
                $error->throw();
            }

            protected function getDefaultTypeErrorMessage(): string
            {
                return 'DEFAULT {value.debug}';
            }
        };
    }

    private
    static function createParserWithBuilder(): Parser
    {
        return new class() implements Parser {
            use OverwritableTypeErrorMessage;

            public function parse(Subject $subject): never
            {
                $builder = new ResultBuilder($subject, 'TestParser');
                $this->logTypeError($builder);
                Assert::fail('Builder did not throw');
            }

            protected function getDefaultTypeErrorMessage(): string
            {
                return 'DEFAULT {value.debug}';
            }
        };
    }

    #[
        \PHPUnit\Framework\Attributes\DataProvider('provideCases')]
    public function testGetDefaultChainDescription(mixed $value, \Closure $builder, string $expectedMessage): void
    {
        /** @var Parser $parser */
        $parser = $builder();
        self::expectException(ParsingException::class);
        $this->expectExceptionMessage($expectedMessage);
        $parser->parse(Subject::default($value));
    }
}
