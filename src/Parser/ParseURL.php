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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

/**
 * Parses the provided string, treating is an URL, and returns the
 * resulting parts.
 *
 * @see parse_url()
 */
class ParseURL implements Parser
{
    use Chainable, OverwritableChainDescription, TypeExceptionMessage;

    private const TARGET_SCHEME = 'scheme',
        TARGET_HOST = 'host',
        TARGET_PORT = 'port',
        TARGET_USER = 'user',
        TARGET_PASS = 'pass',
        TARGET_PATH = 'path',
        TARGET_QUERY = 'query',
        TARGET_FRAGMENT = 'fragment';

    /** @var array<array{string, null|int|string, Parser, string|null}> */
    private array $giveElements = [];

    private string $invalidStringExceptionMessage = 'The provided string could not be parsed as a url';

    private function __construct()
    {
    }

    /**
     * Overwrites the exception thrown in case the provided string cannot be parsed as an url
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     */
    public function setInvalidStringExceptionMessage(string $message): self
    {
        $this->invalidStringExceptionMessage = $message;

        return $this;
    }

    /**
     * @return self
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * If the parsed URL contains a scheme, it will be forwarded to the provided parser
     * If the url does not contain a scheme, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param ParserContract $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveScheme(ParserContract $parser, string $missingExceptionMessage = 'The provided URL does not specify a scheme'): self
    {
        $this->giveElements[] = [self::TARGET_SCHEME, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The scheme contained in the URL will be provided to the parser.
     * If the URL does not contain a scheme, the provided default will be provided to
     * the parser instead
     *
     * @param ParserContract $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveSchemeDefaulted(string $default, ParserContract $parser): self
    {
        $this->giveElements[] = [self::TARGET_SCHEME, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a host, it will be forwarded to the provided parser
     * If the url does not contain a host, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param ParserContract $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveHost(ParserContract $parser, string $missingExceptionMessage = 'The provided URL does not specify a host'): self
    {
        $this->giveElements[] = [self::TARGET_HOST, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The host contained in the URL will be provided to the parser.
     * If the URL does not contain a host, the provided default will be provided to
     * the parser instead
     *
     * @param ParserContract $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveHostDefaulted(string $default, ParserContract $parser): self
    {
        $this->giveElements[] = [self::TARGET_HOST, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a port, it will be forwarded to the provided parser
     * If the url does not contain a port, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param ParserContract $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function givePort(ParserContract $parser, string $missingExceptionMessage = 'The provided URL does not specify a port'): self
    {
        $this->giveElements[] = [self::TARGET_PORT, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The port contained in the URL will be provided to the parser.
     * If the URL does not contain a port, the provided default will be provided to
     * the parser instead
     *
     * @param int $default
     *
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function givePortDefaulted(int $default, ParserContract $parser): self
    {
        $this->giveElements[] = [self::TARGET_PORT, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a user, it will be forwarded to the provided parser
     * If the url does not contain a user, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param ParserContract $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveUser(ParserContract $parser, string $missingExceptionMessage = 'The provided URL does not specify a user'): self
    {
        $this->giveElements[] = [self::TARGET_USER, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The user contained in the URL will be provided to the parser.
     * If the URL does not contain a user, the provided default will be provided to
     * the parser instead
     *
     * @param ParserContract $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveUserDefaulted(string $default, ParserContract $parser): self
    {
        $this->giveElements[] = [self::TARGET_USER, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a password, it will be forwarded to the provided parser
     * If the url does not contain a password, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param ParserContract $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function givePassword(ParserContract $parser, string $missingExceptionMessage = 'The provided URL does not specify a password'): self
    {
        $this->giveElements[] = [self::TARGET_PASS, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The password contained in the URL will be provided to the parser.
     * If the URL does not contain a password, the provided default will be provided to
     * the parser instead
     *
     * @param ParserContract $parser
     * @param string $default
     *
     * @return $this
     */
    public function givePasswordDefaulted(string $default, ParserContract $parser): self
    {
        $this->giveElements[] = [self::TARGET_PASS, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a path, it will be forwarded to the provided parser
     * If the url does not contain a path, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param ParserContract $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function givePath(ParserContract $parser, string $missingExceptionMessage = 'The provided URL does not specify a path'): self
    {
        $this->giveElements[] = [self::TARGET_PATH, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The path contained in the URL will be provided to the parser.
     * If the URL does not contain a path, the provided default will be provided to
     * the parser instead
     *
     * @param ParserContract $parser
     * @param string $default
     *
     * @return $this
     */
    public function givePathDefaulted(string $default, ParserContract $parser): self
    {
        $this->giveElements[] = [self::TARGET_PATH, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a query, it will be forwarded to the provided parser
     * If the url does not contain a query, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param ParserContract $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveQuery(ParserContract $parser, string $missingExceptionMessage = 'The provided URL does not specify a query'): self
    {
        $this->giveElements[] = [self::TARGET_QUERY, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The query contained in the URL will be provided to the parser.
     * If the URL does not contain a query, the provided default will be provided to
     * the parser instead
     *
     * @param ParserContract $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveQueryDefaulted(string $default, ParserContract $parser): self
    {
        $this->giveElements[] = [self::TARGET_QUERY, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a fragment, it will be forwarded to the provided parser
     * If the url does not contain a fragment, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param ParserContract $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveFragment(ParserContract $parser, string $missingExceptionMessage = 'The provided URL does not specify a fragment'): self
    {
        $this->giveElements[] = [self::TARGET_FRAGMENT, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The fragment contained in the URL will be provided to the parser.
     * If the URL does not contain a fragment, the provided default will be provided to
     * the parser instead
     *
     * @param ParserContract $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveFragmentDefaulted(string $default, ParserContract $parser): self
    {
        $this->giveElements[] = [self::TARGET_FRAGMENT, $default, $parser, null];

        return $this;
    }

    /**
     *
     * @inheritDoc
     */
    public function parse($value, ?Path $path = null)
    {
        if (!is_string($value)) {
            $this->throwTypeException($value, $path);
        }

        $parsed = parse_url($value);

        if(!$parsed) {
            throw new ParsingException(
                $value,
                Debug::parseMessage(
                    $this->invalidStringExceptionMessage,
                    ['value' => $value]
                ),
                $path
            );
        }

        $path ??= Path::default($value);
        foreach ($this->giveElements as [$target, $default, $parser, $missingExceptionMessage]) {
            $fieldValue = $parsed[$target] ?? null;
            if ($fieldValue === null) {
                if ($missingExceptionMessage !== null) {
                    throw new ParsingException(
                        $value,
                        Debug::parseMessage($missingExceptionMessage, ['value' => $value]),
                        $path
                    );
                }

                $fieldValue = $default;
            }
            $parser->parse($fieldValue, $path->meta($target));
        }

        return $parsed;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not of type string';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('parse URL', false);
    }
}
