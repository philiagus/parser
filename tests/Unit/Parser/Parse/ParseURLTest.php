<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Test\Unit\Parser\Parse;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\Parse\ParseURL;
use Philiagus\Parser\Test\ChainableParserTestTrait;
use Philiagus\Parser\Test\InvalidValueParserTestTrait;
use Philiagus\Parser\Test\OverwritableTypeErrorMessageTestTrait;
use Philiagus\Parser\Test\ParserTestBase;
use Philiagus\Parser\Test\ValidValueParserTestTrait;
use Philiagus\Parser\Util\Stringify;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ParseURL::class)]
class ParseURLTest extends ParserTestBase
{

    use ChainableParserTestTrait, ValidValueParserTestTrait, InvalidValueParserTestTrait, OverwritableTypeErrorMessageTestTrait;

    public static function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => ParseURL::new(), parse_url($value)])
            ->provide(false);
    }

    public static function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => ParseURL::new()])
            ->provide(false);
    }

    public static function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => ParseURL::new()])
            ->provide(false);
    }

    public static function provideMissingElementCases(): array
    {
        return [
            'missing path' => ['https://example.org', 'Path'],
            'missing user' => ['path', 'User'],
            'missing password' => ['path', 'Password'],
            'missing scheme' => ['path', 'Scheme'],
            'missing host' => ['path', 'Host'],
            'missing fragment' => ['path', 'Fragment'],
            'missing port' => ['path', 'Port'],
        ];
    }

    public function testDefaultingOfEverythingExceptionPath(): void
    {
        ParseURL::new()
            ->giveFragmentDefaulted('fragment default', $this->prophesizeParser([['fragment default']]))
            ->giveHostDefaulted('host default', $this->prophesizeParser([['host default']]))
            ->givePasswordDefaulted('password default', $this->prophesizeParser([['password default']]))
            ->givePortDefaulted(1234, $this->prophesizeParser([[1234]]))
            ->giveQueryDefaulted('query defaulted', $this->prophesizeParser([['query defaulted']]))
            ->giveSchemeDefaulted('scheme defaulted', $this->prophesizeParser([['scheme defaulted']]))
            ->giveUserDefaulted('user defaulted', $this->prophesizeParser([['user defaulted']]))
            ->givePath($this->prophesizeParser([['path']]))
            ->parse(Subject::default('path'));
    }

    public function testDefaultingOfPath(): void
    {
        ParseURL::new()
            ->givePathDefaulted('path default', $this->prophesizeParser([['path default']]))
            ->giveScheme($this->prophesizeParser([['https']]))
            ->giveHost($this->prophesizeParser([['example.org']]))
            ->parse(Subject::default('https://example.org'));
    }

    public function testAllGivings(): void
    {
        ParseURL::new()
            ->giveFragment($this->prophesizeParser([['fragment']]))
            ->giveHost($this->prophesizeParser([['example.org']]))
            ->givePassword($this->prophesizeParser([['password']]))
            ->givePort($this->prophesizeParser([[1234]]))
            ->giveQuery($this->prophesizeParser([['query']]))
            ->giveScheme($this->prophesizeParser([['https']]))
            ->giveUser($this->prophesizeParser([['user']]))
            ->givePath($this->prophesizeParser([['/path']]))
            ->parse(Subject::default('https://user:password@example.org:1234/path?query#fragment'));
    }

    public function testSetInvalidStringErrorMessage(): void
    {
        $builder = $this->builder();
        $builder
            ->test()
            ->arguments(
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn() => true)
            )
            ->value(
                'https://',
                expectSuccess: fn() => false
            );
        $builder->run();
    }

    public function testStringCouldNotBeParsed_messageOverwrite(): void
    {
        $msg = 'MSG {value.raw}';
        $value = 'https://';
        self::expectException(ParsingException::class);
        self::expectExceptionMessage(Stringify::parseMessage($msg, ['value' => $value]));
        ParseURL::new()
            ->setInvalidStringErrorMessage($msg)
            ->parse(Subject::default($value));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideMissingElementCases')]
    public function testMissingElement(string $value, string $target): void
    {
        $builder = $this->builder();
        $builder
            ->test(
                methodName: 'give' . ucfirst($target)
            )
            ->arguments(
                $builder
                    ->parserArgument()
                    ->willBeCalledIf(fn() => false),
                $builder
                    ->messageArgument()
                    ->expectedWhen(fn() => true)
            )
            ->value(
                $value,
                expectSuccess: fn() => false
            );
        $builder->run();
    }
}
