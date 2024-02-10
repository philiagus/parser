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

namespace Philiagus\Parser\Parser\Parse;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\MetaInformation;

/**
 * Parses the provided string, treating is an URL, and returns the
 * resulting parts.
 *
 * @see parse_url()
 */
class ParseURL extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private const string TARGET_SCHEME = 'scheme',
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

    public static function new(): static
    {
        return new static();
    }

    /**
     * Overwrites the exception thrown in case the provided string cannot be parsed as an url
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     */
    public function setInvalidStringExceptionMessage(string $message): static
    {
        $this->invalidStringExceptionMessage = $message;

        return $this;
    }

    /**
     * If the parsed URL contains a scheme, it will be forwarded to the provided parser
     * If the url does not contain a scheme, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveScheme(Parser $parser, string $missingExceptionMessage = 'The provided URL does not specify a scheme'): static
    {
        $this->giveElements[] = [self::TARGET_SCHEME, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The scheme contained in the URL will be provided to the parser.
     * If the URL does not contain a scheme, the provided default will be provided to
     * the parser instead
     *
     * @param Parser $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveSchemeDefaulted(string $default, Parser $parser): static
    {
        $this->giveElements[] = [self::TARGET_SCHEME, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a host, it will be forwarded to the provided parser
     * If the url does not contain a host, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveHost(Parser $parser, string $missingExceptionMessage = 'The provided URL does not specify a host'): static
    {
        $this->giveElements[] = [self::TARGET_HOST, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The host contained in the URL will be provided to the parser.
     * If the URL does not contain a host, the provided default will be provided to
     * the parser instead
     *
     * @param Parser $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveHostDefaulted(string $default, Parser $parser): static
    {
        $this->giveElements[] = [self::TARGET_HOST, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a port, it will be forwarded to the provided parser
     * If the url does not contain a port, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function givePort(Parser $parser, string $missingExceptionMessage = 'The provided URL does not specify a port'): static
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
     * @param Parser $parser
     *
     * @return $this
     */
    public function givePortDefaulted(int $default, Parser $parser): static
    {
        $this->giveElements[] = [self::TARGET_PORT, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a user, it will be forwarded to the provided parser
     * If the url does not contain a user, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveUser(Parser $parser, string $missingExceptionMessage = 'The provided URL does not specify a user'): static
    {
        $this->giveElements[] = [self::TARGET_USER, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The user contained in the URL will be provided to the parser.
     * If the URL does not contain a user, the provided default will be provided to
     * the parser instead
     *
     * @param Parser $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveUserDefaulted(string $default, Parser $parser): static
    {
        $this->giveElements[] = [self::TARGET_USER, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a password, it will be forwarded to the provided parser
     * If the url does not contain a password, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function givePassword(Parser $parser, string $missingExceptionMessage = 'The provided URL does not specify a password'): static
    {
        $this->giveElements[] = [self::TARGET_PASS, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The password contained in the URL will be provided to the parser.
     * If the URL does not contain a password, the provided default will be provided to
     * the parser instead
     *
     * @param Parser $parser
     * @param string $default
     *
     * @return $this
     */
    public function givePasswordDefaulted(string $default, Parser $parser): static
    {
        $this->giveElements[] = [self::TARGET_PASS, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a path, it will be forwarded to the provided parser
     * If the url does not contain a path, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function givePath(Parser $parser, string $missingExceptionMessage = 'The provided URL does not specify a path'): static
    {
        $this->giveElements[] = [self::TARGET_PATH, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The path contained in the URL will be provided to the parser.
     * If the URL does not contain a path, the provided default will be provided to
     * the parser instead
     *
     * @param Parser $parser
     * @param string $default
     *
     * @return $this
     */
    public function givePathDefaulted(string $default, Parser $parser): static
    {
        $this->giveElements[] = [self::TARGET_PATH, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a query, it will be forwarded to the provided parser
     * If the url does not contain a query, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveQuery(Parser $parser, string $missingExceptionMessage = 'The provided URL does not specify a query'): static
    {
        $this->giveElements[] = [self::TARGET_QUERY, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The query contained in the URL will be provided to the parser.
     * If the URL does not contain a query, the provided default will be provided to
     * the parser instead
     *
     * @param Parser $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveQueryDefaulted(string $default, Parser $parser): static
    {
        $this->giveElements[] = [self::TARGET_QUERY, $default, $parser, null];

        return $this;
    }

    /**
     * If the parsed URL contains a fragment, it will be forwarded to the provided parser
     * If the url does not contain a fragment, an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingExceptionMessage
     *
     * @return $this
     */
    public function giveFragment(Parser $parser, string $missingExceptionMessage = 'The provided URL does not specify a fragment'): static
    {
        $this->giveElements[] = [self::TARGET_FRAGMENT, null, $parser, $missingExceptionMessage];

        return $this;
    }

    /**
     * The fragment contained in the URL will be provided to the parser.
     * If the URL does not contain a fragment, the provided default will be provided to
     * the parser instead
     *
     * @param Parser $parser
     * @param string $default
     *
     * @return $this
     */
    public function giveFragmentDefaulted(string $default, Parser $parser): static
    {
        $this->giveElements[] = [self::TARGET_FRAGMENT, $default, $parser, null];

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        $parsed = parse_url($value);

        if (!$parsed) {
            $builder->logErrorUsingDebug(
                $this->invalidStringExceptionMessage
            );

            return $builder->createResultUnchanged();
        }

        foreach ($this->giveElements as [$target, $default, $parser, $missingExceptionMessage]) {
            $fieldValue = $parsed[$target] ?? null;
            if ($fieldValue === null) {
                if ($missingExceptionMessage !== null) {
                    $builder->logErrorUsingDebug($missingExceptionMessage);

                    continue;
                }

                $fieldValue = $default;
            }
            $parser->parse(
                new MetaInformation($builder->getSubject(), $target, $fieldValue)
            );
        }

        return $builder->createResult($parsed);
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type string';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'parse URL';
    }
}