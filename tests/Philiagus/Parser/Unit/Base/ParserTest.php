<?php
/**
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit\Base;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{

    public const ANOTHER_VALUE = 'another value';

    /**
     * @return array
     * @throws \Exception
     */
    public function allValuesProvider(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    /**
     * @dataProvider allValuesProvider
     *
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItWritesByReferenceAndReturnsValue($value): void
    {
        $target = null;
        $parser = new class($target) extends Parser
        {

            private $wasCalled = false;

            public function wasCalled(): bool
            {
                return $this->wasCalled;
            }

            protected function execute($value, Path $path)
            {
                $this->wasCalled = true;

                // make an array out of it so that we can test if it really was used
                return [$value, ParserTest::ANOTHER_VALUE];
            }
        };

        $result = $parser->parse($value);

        self::assertTrue($parser->wasCalled());
        DataProvider::assertSame([$value, ParserTest::ANOTHER_VALUE], $target);
        DataProvider::assertSame([$value, ParserTest::ANOTHER_VALUE], $result);
        DataProvider::assertSame($target, $result);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatChainingWorks(): void
    {
        $resultA = $resultB = $resultC = null;
        $baseParser = new class($resultA) extends Parser
        {
            protected function execute($value, Path $path)
            {
                Assert::assertSame(1, $value);

                return 2;
            }
        };

        $baseParser->then(
            new class($resultB) extends Parser
            {
                protected function execute($value, Path $path)
                {
                    Assert::assertSame(2, $value);

                    return 3;
                }
            }
        );

        $resultC = $baseParser->parse(1);
        self::assertSame(3, $resultA);
        self::assertSame(3, $resultB);
        self::assertSame(3, $resultC);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatStaticConstructorReturnsWorkingInstance(): void
    {
        \Philiagus\Test\Parser\Mock\Parser::new($target)->parse('result');
        self::assertSame('result', $target);
    }
}