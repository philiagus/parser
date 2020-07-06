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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertStringRegex extends Parser
{

    public const DEFAULT_PATTERN_EXCEPTION_MESSAGE = 'The string does not match the expected pattern';

    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of type string';

    /**
     * @var null|false|int
     */
    private $global = null;

    /**
     * @var null|bool
     */
    private $offsetCapture = null;

    /**
     * @var bool|null
     */
    private $unmatchedAsNull = null;

    /**
     * @var ParserContract[]
     */
    private $matchesParser = [];

    /**
     * @var null|string
     */
    private $pattern = null;

    /**
     * @var string|null
     */
    private $patternExceptionMessage = null;

    /**
     * @var null|int
     */
    private $offset = null;

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
        return (new self())->setPattern($pattern, $exceptionMessage);
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
        if ($this->pattern !== null) {
            throw new ParserConfigurationException(
                'The pattern for AssertStringRegex has already been defined and cannot be overwritten'
            );
        }
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
     * Defines the exception message to use if the value is not a string
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function overwriteTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Sets to use preg_match or preg_match_all, depending on the argument.
     * The method can only be called once
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
        if ($this->global !== null) {
            throw new ParserConfigurationException(
                'Global matching configuration of AssertStringRegex has already been defined and cannot be overwritten'
            );
        }

        if (is_bool($matchType)) {
            if (!$matchType) {
                $this->global = false;
            } else {
                $this->global = PREG_PATTERN_ORDER;
            }
        } else if ($matchType === PREG_SET_ORDER || $matchType === PREG_PATTERN_ORDER) {
            $this->global = $matchType;
        } else {
            throw new ParserConfigurationException(
                'Global matching configuration of AssertStringRegex must be provided as bool, PREG_SET_ORDER or PREG_PATTERN_ORDER'
            );
        }

        return $this;
    }

    /**
     * Sets the offset from where to search in the pattern
     * The method can only be called once
     *
     * @param int $offset
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setOffset(int $offset): self
    {
        if ($this->offset !== null) {
            throw new ParserConfigurationException(
                'Offset configuration of AssertStringRegex has already been defined and cannot be overwritten'
            );
        }

        $this->offset = $offset;

        return $this;
    }

    /**
     * Adds the PREG_OFFSET_CAPTURE flag to the regular expression, influencing the
     * resulting matches and the array provided to the matches parsers
     * The method can only be called once
     *
     * @see https://www.php.net/manual/de/function.preg-match-all
     *
     * @param bool $offsetCapture
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setOffsetCapture(bool $offsetCapture = true): self
    {
        if ($this->offsetCapture !== null) {
            throw new ParserConfigurationException(
                'Offset capture configuration of AssertStringRegex has already been defined and cannot be overwritten'
            );
        }

        $this->offsetCapture = $offsetCapture;

        return $this;
    }

    /**
     * Adds the PREG_UNMATCHED_AS_NULL flag to the regular expression, influencing the
     * resulting matches and the array provided to the matches parsers
     * The method can only be called once
     *
     * @see https://www.php.net/manual/de/function.preg-match-all
     *
     * @param bool $unmatchedAsNull
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setUnmatchedAsNull(bool $unmatchedAsNull = true): self
    {
        if ($this->unmatchedAsNull !== null) {
            throw new ParserConfigurationException(
                'Unmatched as null configuration of AssertStringRegex has already been defined and cannot be overwritten'
            );
        }

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
    public function withMatches(ParserContract $parser): self
    {
        $this->matchesParser[] = $parser;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        if ($this->pattern === null) {
            throw new ParserConfigurationException(
                'Called AssertStringRegex without a pattern to match against'
            );
        }

        if (!is_string($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
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

        foreach ($this->matchesParser as $parser) {
            $parser->parse($matches, $path->meta('matches'));
        }

        return $value;
    }
}