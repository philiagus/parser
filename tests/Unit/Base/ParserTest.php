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
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Chainable;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Util\Debug;

/**
 * @covers \Philiagus\Parser\Base\Parser
 */
class ParserTest extends ParserTestBase
{
    use ChainableParserTestTrait;

    public static function provideAnything(): array
    {
        return (new DataProvider())->provide();
    }

    /**
     * @dataProvider provideAnything
     */
    public function testExecute(mixed $sourceValue): void
    {
        $expectedResult = new \stdClass();
        $parser = self::createParser($expectedResult);

        self::assertSame(
            $expectedResult,
            $parser->parse(Subject::default($sourceValue))->getValue()
        );
    }

    private static function createParser(mixed $expectedResult): Contract\Parser&Chainable
    {
        return new class($expectedResult) extends Parser {

            public function __construct(private readonly mixed $expectedResult)
            {
            }

            protected function execute(ResultBuilder $builder): Contract\Result
            {
                return $builder->createResult($this->expectedResult);
            }

            protected function getDefaultParserDescription(Contract\Subject $subject): string
            {
                return 'parser';
            }
        };
    }

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider())
            ->map(
                function ($value) {
                    $expected = new \stdClass();

                    return [$value, fn() => self::createParser($expected), $expected];
                }
            )
            ->provide(false);
    }

    public function test(): void
    {
        $builder = $this->builder();
        $builder
            ->test(
                fn() => self::createParser(null),
                'setParserDescription'
            )
            ->arguments(
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn() => false)
            )
            ->provider(
                DataProvider::TYPE_ALL,
                successValidator: function (Contract\Subject $subject, Contract\Result $result, array $arguments): array {
                    $received = $result->getSourceSubject()->getPathAsString(true);
                    $expectedMessage = Debug::getType($subject->getValue()) . ' ▷' . $arguments[0];
                    if ($received !== $expectedMessage) {
                        return [
                            'Message does not match:' . PHP_EOL .
                            'Got: ' . $received . PHP_EOL .
                            'Expected: ' . $expectedMessage,
                        ];
                    }

                    return [];
                }
            );

        $builder->run();
    }
}
