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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philiagus\Parser\Base\OverwritableTypeErrorMessage
 */
class OverwritableTypeErrorMessageTest extends TestCase
{
    public function provideCases(): array
    {
        $cases = [];
        foreach ((new DataProvider())->provide(false) as $name => $value) {
            $cases["Default Message No Builder -> $name"] = [
                $value,
                function () {
                    return $this->createParserWithoutBuilder();
                },
                Debug::parseMessage('DEFAULT {subject.debug}', ['subject' => $value]),
            ];
            $cases["Custom Message No Builder -> $name"] = [
                $value,
                function () {
                    return $this->createParserWithoutBuilder()->setTypeErrorMessage('CUSTOM {subject.debug}');
                },
                Debug::parseMessage('CUSTOM {subject.debug}', ['subject' => $value]),
            ];
            $cases["Default Message Builder -> $name"] = [
                $value,
                function () {
                    return $this->createParserWithBuilder();
                },
                Debug::parseMessage('DEFAULT {subject.debug}', ['subject' => $value]),
            ];
            $cases["Custom Message Builder -> $name"] = [
                $value,
                function () {
                    return $this->createParserWithBuilder()->setTypeErrorMessage('CUSTOM {subject.debug}');
                },
                Debug::parseMessage('CUSTOM {subject.debug}', ['subject' => $value]),
            ];

        }

        return $cases;
    }

    private function createParserWithoutBuilder(): Parser
    {
        return new class() implements Parser {
            use OverwritableTypeErrorMessage;

            public function parse(\Philiagus\Parser\Contract\Subject $subject): never
            {
                /** @var Error $error */
                $error = $this->getTypeError($subject);
                $error->throw();
            }

            protected function getDefaultTypeErrorMessage(): string
            {
                return 'DEFAULT {subject.debug}';
            }
        };
    }

    private function createParserWithBuilder(): Parser
    {
        return new class() implements Parser {
            use OverwritableTypeErrorMessage;

            public function parse(\Philiagus\Parser\Contract\Subject $subject): never
            {
                $builder = new ResultBuilder($subject, 'TestParser');
                $this->logTypeError($builder);
                Assert::fail('Builder did not throw');
            }

            protected function getDefaultTypeErrorMessage(): string
            {
                return 'DEFAULT {subject.debug}';
            }
        };
    }

    /**
     * @dataProvider provideCases
     */
    public function testGetDefaultChainDescription(mixed $value, \Closure $builder, string $expectedMessage): void
    {
        /** @var Parser $parser */
        $parser = $builder();
        self::expectException(ParsingException::class);
        self::expectErrorMessage($expectedMessage);
        $parser->parse(Subject::default($value));
    }
}
