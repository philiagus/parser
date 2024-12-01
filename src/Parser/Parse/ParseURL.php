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
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\MetaInformation;

/**
 * Parses the provided string, treating is a URL, and returns the resulting parts.
 *
 * While this parser is most times used for extraction (via the give* methods),
 * its result value is exactly the result of the core PHP parse_url function.
 *
 * @see parse_url()
 * @package Parser\Parse
 * @target-type string
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

    protected function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Overwrites the error message in case the provided string cannot be parsed as an url
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $errorMessage
     *
     * @return $this
     */
    public function setInvalidStringErrorMessage(string $errorMessage): static
    {
        $this->invalidStringExceptionMessage = $errorMessage;

        return $this;
    }

    /**
     * If the parsed URL contains a scheme, it will be forwarded to the provided parser
     * If the url does not contain a scheme, an error with the specified message is thrown.
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingErrorMessage
     *
     * @return $this
     */
    public function giveScheme(Parser $parser, string $missingErrorMessage = 'The provided URL does not specify a scheme'): static
    {
        $this->giveElements[] = [self::TARGET_SCHEME, null, $parser, $missingErrorMessage];

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
     * If the url does not contain a host, an error with the specified message is generated.
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingErrorMessage
     *
     * @return $this
     */
    public function giveHost(Parser $parser, string $missingErrorMessage = 'The provided URL does not specify a host'): static
    {
        $this->giveElements[] = [self::TARGET_HOST, null, $parser, $missingErrorMessage];

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
     * If the url does not contain a port, an error with the specified message is generated.
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingErrorMessage
     *
     * @return $this
     */
    public function givePort(Parser $parser, string $missingErrorMessage = 'The provided URL does not specify a port'): static
    {
        $this->giveElements[] = [self::TARGET_PORT, null, $parser, $missingErrorMessage];

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
     * If the url does not contain a user, an error with the specified message is generated.
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingErrorMessage
     *
     * @return $this
     */
    public function giveUser(Parser $parser, string $missingErrorMessage = 'The provided URL does not specify a user'): static
    {
        $this->giveElements[] = [self::TARGET_USER, null, $parser, $missingErrorMessage];

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
     * If the url does not contain a password, an error with the specified message is generated.
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingErrorMessage
     *
     * @return $this
     */
    public function givePassword(Parser $parser, string $missingErrorMessage = 'The provided URL does not specify a password'): static
    {
        $this->giveElements[] = [self::TARGET_PASS, null, $parser, $missingErrorMessage];

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
     * If the url does not contain a path, an error with the specified message is generated.
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingErrorMessage
     *
     * @return $this
     */
    public function givePath(Parser $parser, string $missingErrorMessage = 'The provided URL does not specify a path'): static
    {
        $this->giveElements[] = [self::TARGET_PATH, null, $parser, $missingErrorMessage];

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
     * If the url does not contain a query, an error with the specified message is generated.
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingErrorMessage
     *
     * @return $this
     */
    public function giveQuery(Parser $parser, string $missingErrorMessage = 'The provided URL does not specify a query'): static
    {
        $this->giveElements[] = [self::TARGET_QUERY, null, $parser, $missingErrorMessage];

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
     * If the url does not contain a fragment, an error with the specified message is generated.
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param Parser $parser
     * @param string $missingErrorMessage
     *
     * @return $this
     */
    public function giveFragment(Parser $parser, string $missingErrorMessage = 'The provided URL does not specify a fragment'): static
    {
        $this->giveElements[] = [self::TARGET_FRAGMENT, null, $parser, $missingErrorMessage];

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
    #[\Override] protected function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        $parsed = parse_url($value);

        if (!$parsed) {
            $builder->logErrorStringify(
                $this->invalidStringExceptionMessage
            );

            return $builder->createResultUnchanged();
        }

        foreach ($this->giveElements as [$target, $default, $parser, $missingExceptionMessage]) {
            $fieldValue = $parsed[$target] ?? null;
            if ($fieldValue === null) {
                if ($missingExceptionMessage !== null) {
                    $builder->logErrorStringify($missingExceptionMessage);

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
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'parse URL';
    }
}
