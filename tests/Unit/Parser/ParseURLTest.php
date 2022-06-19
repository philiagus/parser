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

namespace Philiagus\Parser\Test\Unit\Parser;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Parser\ParseURL;
use Philiagus\Parser\Test\ChainableParserTest;
use Philiagus\Parser\Test\InvalidValueParserTest;
use Philiagus\Parser\Test\SetTypeExceptionMessageTest;
use Philiagus\Parser\Test\TestBase;
use Philiagus\Parser\Test\ValidValueParserTest;
use Philiagus\Parser\Util\Debug;
use PHPUnit\Framework\TestCase;

class ParseURLTest extends TestBase
{

    use ChainableParserTest, ValidValueParserTest, InvalidValueParserTest, SetTypeExceptionMessageTest;

    public function provideValidValuesAndParsersAndResults(): array
    {
        return (new DataProvider(DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => ParseURL::new(), parse_url($value)])
            ->provide(false);
    }

    public function provideInvalidValuesAndParsers(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => ParseURL::new()])
            ->provide(false);
    }

    public function provideInvalidTypesAndParser(): array
    {
        return (new DataProvider(~DataProvider::TYPE_STRING))
            ->map(fn($value) => [$value, fn() => ParseURL::new()])
            ->provide(false);
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
            ->parse('path');
    }

    public function testDefaultingOfPath(): void
    {
        ParseURL::new()
            ->givePathDefaulted('path default', $this->prophesizeParser([['path default']]))
            ->giveScheme($this->prophesizeParser([['https']]))
            ->giveHost($this->prophesizeParser([['example.org']]))
            ->parse('https://example.org');
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
            ->parse('https://user:password@example.org:1234/path?query#fragment');
    }

    public function testStringCouldNotBeParsed(): void
    {
        self::expectException(ParsingException::class);
        ParseURL::new()
            ->parse('https://');
    }

    public function testStringCouldNotBeParsed_messageOverwrite(): void
    {
        $msg = 'MSG {value.raw}';
        $value = 'https://';
        self::expectException(ParsingException::class);
        self::expectExceptionMessage(Debug::parseMessage($msg, ['value' => $value]));
        ParseURL::new()
            ->setInvalidStringExceptionMessage($msg)
            ->parse($value);
    }

    public function provideMissingElementCases(): array
    {
        return [
            'missing path' => ['https://example.org', 'Path'],
            'missing user' => ['path', 'User'],
            'missing password' => ['path', 'Password'],
            'missing scheme' => ['path', 'Scheme'],
            'missing host' => ['path', 'Host'],
            'missing fragment' => ['path', 'Fragment'],
            'missing port' => ['path', 'Port'],
            'message overwrite missing path' => ['https://example.org', 'Path', 'MSG {value.raw}'],
            'message overwrite missing user' => ['path', 'User', 'MSG {value.raw}'],
            'message overwrite missing password' => ['path', 'Password', 'MSG {value.raw}'],
            'message overwrite missing scheme' => ['path', 'Scheme', 'MSG {value.raw}'],
            'message overwrite missing host' => ['path', 'Host', 'MSG {value.raw}'],
            'message overwrite missing fragment' => ['path', 'Fragment', 'MSG {value.raw}'],
            'message overwrite missing port' => ['path', 'Port', 'MSG {value.raw}'],
        ];
    }

    /**
     * @dataProvider provideMissingElementCases
     */
    public function testMissingElement(string $value, string $target, ?string $message = null): void
    {
        $method = 'give' . ucfirst($target);

        $parser = ParseURL::new();
        if($message) {
            $parser->$method($this->prophesizeUncalledParser(), $message);
            self::expectExceptionMessage(Debug::parseMessage($message, ['value' => $value]));
        } else {
            $parser->$method($this->prophesizeUncalledParser());
        }
        self::expectException(ParsingException::class);
        $parser->parse($value);
    }
}
