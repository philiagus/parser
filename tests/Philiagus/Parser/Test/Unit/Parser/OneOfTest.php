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

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\OneOfParsingException;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\OneOf;
use Philiagus\Parser\Path\Root;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class OneOfTest extends TestCase
{


    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new OneOf()) instanceof Parser);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItStopsAtTheFirstMatchingParser(): void
    {
        self::assertEquals('matched!', (new OneOf())
            ->addSameOption(INF)
            ->addEqualsOption(NAN)
            ->addOption(
                new class() extends Parser {
                    public function execute($value, Path $path = null)
                    {
                        throw new ParsingException('value', 'Exception', $path);
                    }
                }
            )
            ->addOption(
                new class() extends Parser {
                    public function execute($value, Path $path = null)
                    {
                        return 'matched!';
                    }
                }
            )
            ->addOption(
                new class() extends Parser {
                    public function execute($value, Path $path = null)
                    {
                        throw new \LogicException('This code should never be reached');
                    }
                }
            )->parse(null));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testThatItThrowsAnExceptionWhenNothingMatches(): void
    {
        $exception = new ParsingException('value', 'Exception', new Root(''));
        $option = $this->prophesize(ParserContract::class);
        $option->parse(null, Argument::type(Path::class))->willThrow($exception);
        /** @var ParserContract $parser */
        $parser = $option->reveal();
        $this->expectException(OneOfParsingException::class);
        (new OneOf())
            ->addOption($parser)
            ->parse(null);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testNonOfExceptionMessage(): void
    {
        $msg = 'msg';
        $this->expectException(OneOfParsingException::class);
        $this->expectExceptionMessage($msg);
        (new OneOf())
            ->addOption(
                new class() extends Parser {
                    protected function execute($value, Path $path)
                    {
                        throw new ParsingException($value, 'muh', $path);
                    }
                }
            )
            ->setNonOfExceptionMessage($msg)
            ->parse(null);
    }

    /**
     * @return array
     */
    public function provideEqualsValues(): array
    {
        return [
            ['1', 1],
            [true, 1],
            [false, 0],
            ['1.0', 1.0],
        ];
    }

    /**
     * @param $provided
     * @param $expected
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider  provideEqualsValues
     */
    public function testEqualsOption($provided, $expected): void
    {
        self::assertSame($expected, (new OneOf())->addEqualsOption($provided)->parse($expected));
        self::assertSame($provided, (new OneOf())->addEqualsOption($expected)->parse($provided));
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testSameOption(): void
    {
        self::assertSame(1, (new OneOf())->addSameOption(1)->parse(1));
    }


    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testEqualsException(): void
    {
        $this->expectException(OneOfParsingException::class);
        (new OneOf())->addEqualsOption(100)->parse(0);
    }

    /**
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function testSameException(): void
    {
        $this->expectException(OneOfParsingException::class);
        (new OneOf())->addSameOption(100)->parse(100.0);
    }

    public function testAllTypesException(): void
    {
        $this->expectException(ParsingException::class);
        OneOf::new()
            ->addOption(
                new class extends Parser {
                    protected function execute($value, Path $path)
                    {
                        throw new ParsingException($value, 'no 1', $path);
                    }
                },
                new class extends Parser {
                    protected function execute($value, Path $path)
                    {
                        throw new ParsingException($value, 'no 2', $path);
                    }
                }
            )
            ->addSameOption(
                1, 2, 3
            )
            ->addEqualsOption(
                1, 2, 3
            )->parse('not existing');
    }


    public function testAllSetNonOfExceptionMessageReplacers(): void
    {
        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            '5 integer integer 5'
        );
        (new OneOf())
            ->setNonOfExceptionMessage('{value} {value.type} {value.debug}')
            ->parse(5);
    }

    public function testDefaultIsReturnedIfNothingMatches(): void
    {
        $object = new \stdClass();
        $parser = OneOf::new()
            ->addSameOption(1,2,3,4,5,6)
            ->setDefaultResult($object);
        self::assertSame($object, $parser->parse('something'));
    }

    public function provideAnyValue(): array
    {
        return (new DataProvider(DataProvider::TYPE_ALL))->provide();
    }

    /**
     * @param $value
     *
     * @throws ParserConfigurationException
     * @throws ParsingException
     * @dataProvider provideAnyValue
     */
    public function testDefaultTakesAnyValue($value): void
    {
        self::assertTrue(DataProvider::isSame(
            $value,
            OneOf::new()->setDefaultResult($value)->parse(0)
        ));
    }

    public function testDefaultNotReturnedOnMatch(): void
    {
        self::assertSame(
            1,
            OneOf::new()->addSameOption(1)->setDefaultResult('false')->parse(1)
        );
    }

}