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
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\ChainableParser;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Util\Debug;

class AssertString implements ChainableParser
{
    use Chainable;

    /** @var string */
    private string $typeExceptionMessage = 'Provided value is not of type string';

    /** @var callable[] */
    private array $assertionList = [];

    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function new(): self
    {
        return new self();
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
    public function setTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Executes strlen on the string and hands the result over to the parser
     *
     * @param ParserContract $integerParser
     *
     * @return $this
     */
    public function giveLength(ParserContract $integerParser): self
    {
        $this->assertionList[] = function (string $value, Path $path) use ($integerParser) {
            $integerParser->parse(strlen($value), $path->meta('length'));
        };

        return $this;
    }

    public function parse($value, ?Path $path = null)
    {
        if (!is_string($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        $path = $path ?? new Root();
        foreach ($this->assertionList as $assertion) {
            $assertion($value, $path);
        }

        return $value;
    }

    /**
     * Performs substr on the string and executes the parser on that part of the string
     *
     * @param int $start
     * @param int|null $length
     * @param ParserContract $stringParser
     *
     * @return $this
     */
    public function giveSubstring(
        int            $start,
        ?int           $length,
        ParserContract $stringParser
    ): self
    {
        $this->assertionList[] = function (string $value, Path $path) use ($start, $length, $stringParser) {
            if ($value === '') {
                $part = '';
            } else {
                $part = (string) substr($value, $start, $length);
            }
            $stringParser->parse($part, $path->meta("$start:$length"));
        };

        return $this;
    }

    /**
     * Matches the provided string against the defined regular expression
     *
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - pattern: The provided regular expression
     *
     * @param string $pattern
     * @param string|null $exceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function assertRegex(
        string $pattern,
        string $exceptionMessage = 'The string does not match the expected pattern'
    ): self
    {
        if (@preg_match($pattern, '') === false) {
            throw new ParserConfigurationException(
                'An invalid regular expression was provided'
            );
        }

        $this->assertionList[] = function (string $value, Path $path) use ($pattern, $exceptionMessage) {
            if (!preg_match($pattern, $value)) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage(
                        $exceptionMessage,
                        [
                            'value' => $value,
                            'pattern' => $pattern,
                        ]
                    ),
                    $path
                );
            }
        };

        return $this;
    }

    /**
     * Checks that the string starts with the provided string and fails if it doesn't
     *
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The expected string
     *
     * @param string $string
     * @param string $message
     *
     * @return $this
     */
    public function assertStartsWith(
        string $string,
        string $message = 'The string does not start with {expected.debug}'
    ): self
    {
        $this->assertionList[] = function (string $value, Path $path) use ($string, $message) {
            if (substr($value, 0, strlen($string)) !== $string) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($message, ['value' => $value, 'expected' => $string]),
                    $path
                );
            }
        };

        return $this;
    }

    /**
     * Checks that the string ends with the provided string and fails if it doesn't
     *
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The expected string
     *
     * @param string $string
     * @param string $message
     *
     * @return $this
     */
    public function assertEndsWith(
        string $string,
        string $message = 'The string does not end with {expected.debug}'
    ): self
    {
        $this->assertionList[] = function (string $value, Path $path) use ($string, $message) {
            if (substr($value, -strlen($string)) !== $string) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($message, ['value' => $value, 'expected' => $string]),
                    $path
                );
            }
        };

        return $this;
    }
}
