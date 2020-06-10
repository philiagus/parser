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

namespace Philiagus\Parser\Test\Unit\Base;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Test\Provider\DataProvider;
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
        $parser = new class($target) extends Parser {

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
        $resultA = $resultB = $resultC = $resultD = null;
        $baseParser = new class($resultA) extends Parser {
            protected function execute($value, Path $path)
            {
                Assert::assertSame(1, $value);

                return 2;
            }
        };

        $baseParser->then(
            new class($resultB) extends Parser {
                protected function execute($value, Path $path)
                {
                    Assert::assertSame(2, $value);

                    return 3;
                }
            }
        );

        $baseParser->then(
            new class($resultC) extends Parser {
                protected function execute($value, Path $path)
                {
                    Assert::assertSame(3, $value);

                    return 4;
                }
            }
        );

        $resultD = $baseParser->parse(1);
        self::assertSame(4, $resultA);
        self::assertSame(3, $resultB);
        self::assertSame(4, $resultC);
        self::assertSame(4, $resultD);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatStaticConstructorReturnsWorkingInstance(): void
    {
        \Philiagus\Parser\Test\Mock\Parser::new($target)->parse('result');
        self::assertSame('result', $target);
    }

    public function testSetParsingExceptionOverwrite(): void
    {
        $parser = new class() extends Parser {

            protected function execute($value, Path $path)
            {
                throw new ParsingException($value, 'some text', $path);
            }
        };

        $parser->setParsingExceptionOverwrite('{value} {value.type} {value.debug}');

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('2 integer integer 2');
        $parser->parse(2);
    }

    public function testSetParsingExceptionOverwriteNull(): void
    {
        $parser = new class() extends Parser {

            protected function execute($value, Path $path)
            {
                throw new ParsingException($value, 'some text', $path);
            }
        };

        $parser->setParsingExceptionOverwrite(null);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('some text');
        $parser->parse(2);
    }

    public function testSetParsingExceptionOverwriteNullBlockRecall(): void
    {
        $parser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };

        $parser->setParsingExceptionOverwrite(null);
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage("The ParsingException overwrite for this parser was already set");
        $parser->setParsingExceptionOverwrite('overwrite');
    }

    public function testSetParsingExceptionOverwriteBlockRecall(): void
    {
        $parser = new class() extends Parser {
            protected function execute($value, Path $path)
            {
            }
        };

        $parser->setParsingExceptionOverwrite('set');
        $this->expectException(ParserConfigurationException::class);
        $this->expectExceptionMessage("The ParsingException overwrite for this parser was already set");
        $parser->setParsingExceptionOverwrite('overwrite');
    }
}