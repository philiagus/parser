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
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Util\Debug;
use Philiagus\Parser\Contract;

/**
 * @covers \Philiagus\Parser\Base\OverwritableParserDescription
 */
class OverwritableParserDescriptionTest extends ParserTestBase
{
    public function test(): void
    {
        $builder = $this->builder();
        $builder
            ->test(
                function () {
                    return new class() implements Parser {
                        use OverwritableParserDescription;

                        protected function getDefaultParserDescription(Contract\Subject $subject): string
                        {
                            return 'default';
                        }

                        public function parse(Contract\Subject $subject): Contract\Result
                        {
                            return $this->createResultBuilder($subject)->createResultUnchanged();
                        }
                    };
                },
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
