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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Util\Debug;

class AssertStringMultibyte extends Base\Parser
{
    use TypeExceptionMessage;

    /** @var string[]|null */
    private ?array $encoding = null;
    /** @var \SplDoublyLinkedList<\Closure> */
    private \SplDoublyLinkedList $assertionList;
    /** @var string */
    private string $encodingDetectionExceptionMessage = 'The encoding of the multibyte string could not be determined';

    /** @var string[]|null */
    private ?array $availableEncodings = ['auto'];

    private function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }

    /**
     * @return self
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * If no encoding is set we try to detect the encoding using mb_detect_encoding($value, "auto", true)
     * The method defines the exception message thrown if the encoding could not be detected that way
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string[] $encodings
     * @param string $message
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setAvailableEncodings(array $encodings, string $message = 'The provided string does not match any expected encoding'): self
    {
        $this->assertEncodings($encodings);
        $this->availableEncodings = $encodings;
        $this->encodingDetectionExceptionMessage = $message;

        return $this;
    }

    /**
     * Asserts a list of encodings and throws an exception if one isn't supported
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
                    Debug::parseMessage("Non-string provided as encoding: {encoding.debug}", ['encoding' => $encoding])
                );
            }
            if (!in_array($encoding, $availableEncodings)) {
                throw new ParserConfigurationException("The encoding $encoding is unknown to the system");
            }
        }
    }

    /**
     * Executes mb_strlen on the string and hands the result over to the parser
     * The encoding will be guessed if not defined using setEncoding
     *
     * @param ParserContract $integerParser
     *
     * @return $this
     */
    public function giveLength(ParserContract $integerParser): self
    {
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($integerParser): void {
            $builder->incorporateChildResult(
                $integerParser->parse(
                    new MetaInformation($builder->getSubject(), 'length in ' . $encoding, mb_strlen($value, $encoding))
                )
            );
        };

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        if ($this->encoding) {
            [$encoding, $exception] = $this->encoding;
            if (!mb_check_encoding($value, $encoding)) {
                $builder->logErrorUsingDebug(
                    $exception,
                    ['encoding' => $encoding]
                );

                return $builder->createResultUnchanged();
            }
        } else {
            $encoding = mb_detect_encoding($value, $this->availableEncodings, true);
            if (!$encoding) {
                $builder->logErrorUsingDebug(
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

    /**
     * Performs mb_substr on the string and executes the parser on that part of the string
     * The encoding will be guessed if not defined using setEncoding
     *
     * @param int $start
     * @param null|int $length
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
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($start, $length, $stringParser): void {
            if ($value === '') {
                $part = '';
            } else {
                $part = mb_substr($value, $start, $length, $encoding);
            }
            $builder->incorporateChildResult(
                $stringParser->parse(
                    new MetaInformation($builder->getSubject(), "$encoding substring from $start to " . ($length ?? 'end'), $part)
                )
            );
        };

        return $this;
    }

    /**
     * Defines the encoding of the string. The code is checked to have this encoding
     * and every other method uses this encoding.
     *
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - encoding: The specified encoding
     *
     * @param string $encoding
     * @param string $exception
     *
     * @return $this
     * @throws ParserConfigurationException
     * @see Debug::parseMessage()
     *
     */
    public function setEncoding(string $encoding, string $exception = 'Multibyte string does not appear to be of the requested encoding'): self
    {
        $this->assertEncodings([$encoding]);

        $this->encoding = [$encoding, $exception];

        return $this;
    }

    /**
     * Checks that the string starts with the provided string and fails if it doesn't.
     * Compares the binary of the strings, so the encoding is not relevant
     *
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
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
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($string, $message): void {
            if (!str_starts_with($value, $string)) {
                $builder->logErrorUsingDebug(
                    $message,
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
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
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
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($string, $message): void {
            if (!str_ends_with($value, $string)) {
                $builder->logErrorUsingDebug(
                    $message,
                    ['expected' => $string]
                );
            }
        };

        return $this;
    }

    /**
     * Provides the set or detected encoding to the defined parser
     *
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveEncoding(Parser $parser): self
    {
        $this->assertionList[] = static function (string $value, $encoding, ResultBuilder $builder) use ($parser) {
            $builder->incorporateChildResult(
                $parser->parse(
                    new MetaInformation($builder->getSubject(), 'encoding', $encoding)
                )
            );
        };

        return $this;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not of type string';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        if ($this->encoding) {
            return "assert {$this->encoding[0]} string";
        }

        return 'assert detected multibyte string';
    }
}
