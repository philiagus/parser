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

namespace Philiagus\Parser\Parser\Assert;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Util\Stringify;

/**
 * Parser used to assert that a value is a string in a certain encoding
 *
 * @package Parser\Assert
 * @target-type string
 */
class AssertStringMultibyte extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var array{"0":string, "1": string}|null */
    private ?array $encoding = null;
    /** @var \SplDoublyLinkedList<\Closure> */
    private \SplDoublyLinkedList $assertionList;
    /** @var string */
    private string $encodingDetectionExceptionMessage = 'The encoding of the multibyte string could not be determined';

    /** @var string[]|null */
    private ?array $availableEncodings = ['auto'];

    protected function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Creates a new instance of this parser that checks and treats the value as
     * being a value of the defined encoding
     *
     * @param string $encoding
     * @param string $errorMessage
     *
     * @return static
     */
    public static function ofEncoding(string $encoding, string $errorMessage = 'Multibyte string does not appear to be encoded in the requested encoding'): static
    {
        return (new static())->setEncoding($encoding, $errorMessage);
    }

    /**
     * Defines the encoding of the string. The code is checked to have this encoding
     * and every other method uses this encoding.
     *
     * The error message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - encoding: The specified encoding
     *
     * @param string $encoding
     * @param string $errorMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     * @see Stringify::parseMessage()
     *
     */
    public function setEncoding(string $encoding, string $errorMessage = 'Multibyte string does not appear to be encoded in {encoding}'): static
    {
        $this->assertEncodings([$encoding]);

        $this->encoding = [$encoding, $errorMessage];

        return $this;
    }

    /**
     * Asserts a list of encodings and generates an error if one isn't supported
     *
     * @param string[] $encodings
     *
     * @return void
     * @throws ParserConfigurationException
     */
    private function assertEncodings(array $encodings): void
    {
        static $availableEncodings = null;
        if ($availableEncodings === null) {
            $availableEncodings = mb_list_encodings();
        }

        foreach ($encodings as $encoding) {
            if (!is_string($encoding)) {
                throw new ParserConfigurationException(
                    Stringify::parseMessage("Non-string provided as encoding: {encoding.debug}", ['encoding' => $encoding])
                );
            }
            if (!in_array($encoding, $availableEncodings)) {
                throw new ParserConfigurationException("The encoding $encoding is unknown to the system");
            }
        }
    }

    /**
     * Creates a new instance of this parser, setting the expected and used encoding to UTF-8
     *
     * @param string $errorMessage
     *
     * @return static
     */
    public static function UTF8(string $errorMessage = 'Multibyte string does not appear to be encoded in UTF-8'): static
    {
        return (new static())->setEncoding('UTF-8', $errorMessage);
    }

    /**
     * If no encoding is set we try to detect the encoding using mb_detect_encoding($value, "auto", true)
     * The method defines the error message used if the encoding could not be detected that way
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string[] $encodings
     * @param string $errorMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setAvailableEncodings(array $encodings, string $errorMessage = 'The provided string does not match any expected encoding'): static
    {
        $this->assertEncodings($encodings);
        $this->availableEncodings = $encodings;
        $this->encodingDetectionExceptionMessage = $errorMessage;

        return $this;
    }

    /**
     * Executes mb_strlen on the string and hands the result over to the parser
     * The encoding will be guessed if not defined using setEncoding
     *
     * @param Parser $integerParser
     *
     * @return $this
     */
    public function giveLength(Parser $integerParser): static
    {
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($integerParser): void {
            $builder->unwrapResult(
                $integerParser->parse(
                    new MetaInformation($builder->getSubject(), 'length in ' . $encoding, mb_strlen($value, $encoding))
                )
            );
        };

        return $this;
    }

    /**
     * Performs mb_substr on the string and executes the parser on that part of the string
     * The encoding will be guessed if not defined using setEncoding
     *
     * @param int $start
     * @param null|int $length
     * @param Parser $stringParser
     *
     * @return $this
     */
    public function giveSubstring(
        int    $start,
        ?int   $length,
        Parser $stringParser
    ): static
    {
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($start, $length, $stringParser): void {
            if ($value === '') {
                $part = '';
            } else {
                $part = mb_substr($value, $start, $length, $encoding);
            }
            $builder->unwrapResult(
                $stringParser->parse(
                    new MetaInformation($builder->getSubject(), "$encoding substring from $start to " . ($length ?? 'end'), $part)
                )
            );
        };

        return $this;
    }

    /**
     * Checks that the string starts with the provided string and fails if it doesn't.
     * Compares the binary of the strings, so the encoding is not relevant
     *
     * The error message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The expected string
     *
     * @param string $string
     * @param string $errorMessage
     *
     * @return $this
     */
    public function assertStartsWith(
        string $string,
        string $errorMessage = 'The string does not start with {expected.debug}'
    ): static
    {
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($string, $errorMessage): void {
            if (!str_starts_with($value, $string)) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['expected' => $string]
                );
            }
        };

        return $this;
    }

    /**
     * Checks that the string ends with the provided string and fails if it doesn't.
     * Compares the binary of the strings, so the encoding is not relevant
     *
     * The error message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The expected string
     *
     * @param string $string
     * @param string $errorMessage
     *
     * @return $this
     */
    public function assertEndsWith(
        string $string,
        string $errorMessage = 'The string does not end with {expected.debug}'
    ): static
    {
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($string, $errorMessage): void {
            if (!str_ends_with($value, $string)) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['expected' => $string]
                );
            }
        };

        return $this;
    }

    /**
     * Provides the set or detected encoding to the defined parser
     *
     * @param Parser $parser
     *
     * @return $this
     */
    public function giveEncoding(Parser $parser): static
    {
        $this->assertionList[] =
            static function (string $value, $encoding, ResultBuilder $builder)
            use ($parser): void {
                $builder->unwrapResult(
                    $parser->parse(
                        new MetaInformation($builder->getSubject(), 'encoding', $encoding)
                    )
                );
            };

        return $this;
    }

    /**
     * Asserts the string matches the provided regular expression.
     *
     * The regular expression is evaluated using `preg_match`.
     *
     *  The error message is processed using Stringify::parseMessage and receives the following elements:
     *  - value: The value currently being parsed
     *  - pattern: The provided pattern
     *
     * @param string $pattern
     * @param string $errorMessage
     * @return $this
     * @see preg_match()
     * @see Stringify::parseMessage()
     */
    public function assertRegex(string $pattern, string $errorMessage = 'The string does not match the expected pattern'): static
    {
        if (@preg_match($pattern, '') === false) {
            throw new ParserConfigurationException(
                'An invalid regular expression was provided'
            );
        }

        $this->assertionList[] =
            static function (string $value, $encoding, ResultBuilder $builder)
            use ($pattern, $errorMessage): void {
                if (!preg_match($pattern, $value)) {
                    $builder->logErrorStringify(
                        $errorMessage,
                        ['pattern' => $pattern]
                    );
                }
            };

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

        if ($this->encoding) {
            [$encoding, $errorMessage] = $this->encoding;
            if (!mb_check_encoding($value, $encoding)) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['encoding' => $encoding]
                );

                return $builder->createResultUnchanged();
            }
        } else {
            $encoding = mb_detect_encoding($value, $this->availableEncodings, true);
            if (!$encoding) {
                $builder->logErrorStringify(
                    $this->encodingDetectionExceptionMessage,
                );

                return $builder->createResultUnchanged();
            }
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($value, $encoding, $builder);
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type string';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        if ($this->encoding) {
            return "assert {$this->encoding[0]} string";
        }

        return 'assert detected multibyte string';
    }
}
