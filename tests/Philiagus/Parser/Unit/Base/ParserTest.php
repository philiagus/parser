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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Type\AcceptsMixed;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{

    public const ANOTHER_VALUE = 'another value';

    public function allValuesProvider(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    /**
     * @dataProvider allValuesProvider
     *
     * @param $value
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

            protected function execute($value, string $path)
            {
                $this->wasCalled = true;

                // make an array out of it so that we can test if it really was used
                return [$value, ParserTest::ANOTHER_VALUE];
            }
        };

        $result = $parser->parse($value);

        self::assertTrue($parser->wasCalled());
        if (is_float($value) && is_nan($value)) {
            // assert reference target
            self::assertTrue(is_array($target));
            self::assertSame([0, 1], array_keys($target));
            self::assertTrue(is_nan($target[0]));
            self::assertSame(ParserTest::ANOTHER_VALUE, $target[1]);

            // assert result
            self::assertTrue(is_array($result));
            self::assertSame([0, 1], array_keys($result));
            self::assertTrue(is_nan($result[0]));
            self::assertSame(ParserTest::ANOTHER_VALUE, $result[1]);
        } else {
            self::assertSame([$value, ParserTest::ANOTHER_VALUE], $target);
            self::assertSame([$value, ParserTest::ANOTHER_VALUE], $result);
            self::assertSame($target, $result);
        }
    }

    public function testRecoveryWithoutError(): void
    {
        $recoveryParser = $this->prophesize(AcceptsMixed::class);
        $recoveryParser->parse()->shouldNotBeCalled();
        self::assertSame(
            null,
            (new class() extends Parser
            {
                protected function execute($value, Path $path)
                {
                    return $value;
                }
            })->recovery($recoveryParser->reveal())->parse(null)
        );
    }

    public function testRecoveryWithError(): void
    {
        $recoveryParser = $this->prophesize(AcceptsMixed::class);
        $recoveryParser->parse(null, '')->shouldBeCalledTimes(1);
        self::assertSame(
            null,
            (new class() extends Parser
            {
                /**
                 * Real conversion of the provided value into the target value
                 * This must be individually implemented by the implementing parser class
                 *
                 * @param mixed $value
                 * @param Path $path
                 *
                 * @return mixed
                 * @throws ParsingException
                 */
                protected function execute($value, Path $path)
                {
                    throw new ParsingException($value, 'should not surface', $path);
                }
            })->recovery($recoveryParser->reveal())->parse(null)
        );
    }

    public function testpipeToWithoutError(): void
    {

        $pipeToParser = $this->prophesize(AcceptsMixed::class);
        $pipeToParser->parse('success value', '')->shouldBeCalledTimes(1)->willReturn('final value');
        self::assertSame(
            'final value',
            (new class() extends Parser
            {
                protected function execute($value, Path $path)
                {
                    return 'success value';
                }
            })->pipeTo($pipeToParser->reveal())->parse(null)
        );
    }

    public function testPipeToWithError(): void
    {

        $pipeToParser = $this->prophesize(AcceptsMixed::class);
        $pipeToParser->parse('success value', '')->shouldNotBeCalled();

        self::expectException(ParsingException::class);
        self::assertSame(
            'final value',
            (new class() extends Parser
            {
                protected function execute($value, Path $path)
                {
                    throw new ParsingException($value, 'exception', $path);
                }
            })->pipeTo($pipeToParser->reveal())->parse(null)
        );
    }

    public function testPostprocessWithError(): void
    {

        $recoveryParser = $this->prophesize(AcceptsMixed::class);
        $recoveryParser->parse(null, '')->shouldBeCalledTimes(1)->willReturn('error value');

        $postprocessParser = $this->prophesize(AcceptsMixed::class);
        $postprocessParser->parse('error value', '')->shouldBeCalledTimes(1)->willReturn('Postprocess value');
        self::assertSame(
            'Postprocess value',
            (new class() extends Parser
            {
                protected function execute($value, Path $path)
                {
                    throw new ParsingException($value, 'should not surface', $path);
                }
            })
                ->recovery($recoveryParser->reveal())
                ->postprocess($postprocessParser->reveal())
                ->parse(null)
        );
    }

}