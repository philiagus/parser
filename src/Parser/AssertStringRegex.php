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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertStringRegex implements Parser
{
    use Chainable, OverwritableChainDescription, TypeExceptionMessage;

    public const DEFAULT_PATTERN_EXCEPTION_MESSAGE = 'The string does not match the expected pattern';
    /** @var null|false|int */
    private $global = null;
    /** @var null|bool */
    private ?bool $offsetCapture = null;
    /** @var null|bool */
    private ?bool $unmatchedAsNull = null;
    /** @var ParserContract[] */
    private array $matchesParser = [];
    /** @var string */
    private string $pattern;
    /** @var null|string */
    private ?string $patternExceptionMessage = null;
    /** @var null|int */
    private ?int $offset = null;

    /** @var Parser[] */
    private array $numberMatchesParsers = [];

    /**
     * AssertStringRegex constructor.
     *
     * @param string $pattern
     * @param string $exceptionMessage
     *
     * @throws ParserConfigurationException
     */
    private function __construct(string $pattern, string $exceptionMessage)
    {
        $this->setPattern($pattern, $exceptionMessage);
    }

    /**
     * Defines the pattern to be matched against with this regular expression.
     * This methods must be called in order for the parser to work correctly.
     * The method can only be called once
     *
     *
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - pattern: The provided regular expression
     *
     * @param string $pattern
     * @param string $exceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setPattern(
        string $pattern,
        string $exceptionMessage = self::DEFAULT_PATTERN_EXCEPTION_MESSAGE
    ): self
    {
        if (@preg_match($pattern, '') === false) {
            throw new ParserConfigurationException(
                'An invalid regular expression was provided'
            );
        }
        $this->pattern = $pattern;
        $this->patternExceptionMessage = $exceptionMessage;

        return $this;
    }

    /**
     * Shorthand for creation of the parser and defining a regular expression for it
     *
     *
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - pattern: The provided regular expression
     *
     * @param string $pattern
     * @param string $exceptionMessage
     *
     * @return static
     * @throws ParserConfigurationException
     */
    public static function pattern(string $pattern, string $exceptionMessage = self::DEFAULT_PATTERN_EXCEPTION_MESSAGE): self
    {
        return new self($pattern, $exceptionMessage);
    }

    /**
     * Sets to use preg_match or preg_match_all, depending on the argument
     *
     * The argument can be
     * false: preg_match will be used
     * true: preg_match_all will be used
     * PREG_SET_ORDER: preg_match_all with the flag PREG_SET_ORDER will be used
     * PREG_PATTERN_ORDER: will result in preg_match_all with the flag PREG_PATTERN_ORDER being used
     *
     * @see https://www.php.net/manual/de/function.preg-match-all
     * @see https://www.php.net/manual/de/function.preg-match
     *
     * @param bool|int $matchType
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setGlobal($matchType): self
    {
        if ($matchType === false) {
            $this->global = false;

            return $this;
        }

        if ($matchType === true) {
            $this->global = PREG_PATTERN_ORDER;

            return $this;
        }

        if ($matchType === PREG_SET_ORDER || $matchType === PREG_PATTERN_ORDER) {
            $this->global = $matchType;

            return $this;
        }

        throw new ParserConfigurationException(
            'Global matching configuration of AssertStringRegex must be provided as bool, PREG_SET_ORDER or PREG_PATTERN_ORDER'
        );
    }

    /**
     * Sets the offset from where to search in the pattern
     *
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Adds the PREG_OFFSET_CAPTURE flag to the regular expression, influencing the
     * resulting matches and the array provided to the matches parsers
     *
     * @see https://www.php.net/manual/de/function.preg-match-all
     *
     * @param bool $offsetCapture
     *
     * @return $this
     */
    public function setOffsetCapture(bool $offsetCapture = true): self
    {
        $this->offsetCapture = $offsetCapture;

        return $this;
    }

    /**
     * Adds the PREG_UNMATCHED_AS_NULL flag to the regular expression, influencing the
     * resulting matches and the array provided to the matches parsers
     *
     * @see https://www.php.net/manual/de/function.preg-match-all
     *
     * @param bool $unmatchedAsNull
     *
     * @return $this
     */
    public function setUnmatchedAsNull(bool $unmatchedAsNull = true): self
    {
        $this->unmatchedAsNull = $unmatchedAsNull;

        return $this;
    }

    /**
     * The matches generated by the regular expressions are forwarded to the defined parser
     *
     * @see https://www.php.net/manual/de/function.preg-match-all
     * @see https://www.php.net/manual/de/function.preg-match
     *
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveMatches(ParserContract $parser): self
    {
        $this->matchesParser[] = $parser;

        return $this;
    }

    public function parse($value, ?Path $path = null)
    {
        if (!is_string($value)) {
            $this->throwTypeException($value, $path);
        }

        $flags = 0;
        $global = false;
        if ($this->global) {
            $global = true;
            $flags = $this->global;
        }

        if ($this->unmatchedAsNull) {
            $flags |= PREG_UNMATCHED_AS_NULL;
        }

        if ($this->offsetCapture) {
            $flags |= PREG_OFFSET_CAPTURE;
        }

        if ($global) {
            $result = preg_match_all($this->pattern, $value, $matches, $flags, $this->offset ?? 0);
        } else {
            $result = preg_match($this->pattern, $value, $matches, $flags, $this->offset ?? 0);
        }

        if (!$result) {
            throw new ParsingException(
                $value,
                Debug::parseMessage(
                    $this->patternExceptionMessage,
                    [
                        'value' => $value,
                        'pattern' => $this->pattern,
                    ]
                ),
                $path
            );
        }

        $path ??= Path::default($value);
        $metaPath = $path->meta('matches');

        foreach($this->numberMatchesParsers as $numberMatchesParser) {
            $numberMatchesParser->parse($result, $path->meta('number of matches'));
        }

        foreach ($this->matchesParser as $parser) {
            $parser->parse($matches, $metaPath);
        }

        return $value;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not of type string';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert string regex', false);
    }

    /**
     * Adds a parser the number of matches are passed to
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveNumberOfMatches(Parser $parser): self
    {
        $this->numberMatchesParsers[] = $parser;

        return $this;
    }
}
