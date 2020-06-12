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

class AssertStringMultibyte extends Parser
{

    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of type string';

    /**
     * @var string[]|null
     */
    private $encoding = null;

    /**
     * @var callable[]
     */
    private $assertionList = [];

    /**
     * @var string
     */
    private $encodingDetectionExceptionMessage = 'The encoding of the multibyte string could not be determined';

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
     * If no encoding is set we try to detect the encoding using mb_detect_encoding($value, "auto", true)
     * The method defines the exception message thrown if the encoding could not be detected that way
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     */
    public function overwriteEncodingDetectionExceptionMessage(string $message): self
    {
        $this->encodingDetectionExceptionMessage = $message;

        return $this;
    }

    /**
     * Executes mb_strlen on the string and hands the result over to the parser
     * The encoding will be guessed if not defined using setEncoding
     *
     * @param ParserContract $integerParser
     *
     * @return $this
     */
    public function withLength(ParserContract $integerParser): self
    {
        $this->assertionList[] = function (string $value, $encoding, Path $path) use ($integerParser) {
            $integerParser->parse(mb_strlen($value, $encoding), $path->meta('length'));
        };

        return $this;
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
    public function withSubstring(
        int $start,
        ?int $length,
        ParserContract $stringParser
    ): self
    {
        $this->assertionList[] = function (string $value, $encoding, Path $path) use ($start, $length, $stringParser) {
            if ($value === '') {
                $part = '';
            } else {
                $part = (string) mb_substr($value, $start, $length, $encoding);
            }
            $stringParser->parse($part, $path->meta("$start:$length"));
        };

        return $this;
    }

    /**
     * Defines the encoding of the string. The code is checked to have this encoding
     * and every other method uses this encoding.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
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
        static $encodings = null;

        if ($this->encoding !== null) {
            throw new ParserConfigurationException(
                'The encoding of AssertStringMultibyte has already been defined and cannot be overwritten'
            );
        }

        if ($encodings === null) {
            $encodings = mb_list_encodings();
        }

        if (!in_array($encoding, $encodings)) {
            throw new ParserConfigurationException("The encoding $encoding is unknown to the system");
        }

        $this->encoding = [$encoding, $exception];

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_string($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        if ($this->encoding) {
            [$encoding, $exception] = $this->encoding;
            if (!mb_check_encoding($value, $encoding)) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($exception, ['value' => $value, 'encoding' => $encoding]),
                    $path
                );
            }
        } else {
            $encoding = mb_detect_encoding($value, "auto", true);
            if (!$encoding) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($this->encodingDetectionExceptionMessage, ['value' => $value]),
                    $path
                );
            }
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($value, $encoding, $path);
        }

        return $value;
    }
}