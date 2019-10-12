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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;

class AssertStringMultibyte extends Parser
{

    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of type string';

    /**
     * @var null|Parser
     */
    private $length = null;

    /**
     * @var mixed[]
     */
    private $substring = [];

    /**
     * @var string[]
     */
    private $encoding = [];

    /**
     * Defines the exception message to use if the value is not a string
     *
     * @param string $message
     *
     * @return $this
     */
    public function withTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Executes mb_strlen on the string and hands the result over to the parser
     *
     * @param Parser $integerParser
     *
     * @return $this
     */
    public function withLength(Parser $integerParser): self
    {
        $this->length = $integerParser;

        return $this;
    }

    /**
     * Performs mb_substr on the string and executes the parser on that part of the string
     *
     * @param int $start
     * @param null|int $length
     * @param Parser $stringParser
     *
     * @return $this
     */
    public function withSubstring(
        int $start,
        ?int $length,
        Parser $stringParser
    ): self
    {
        $this->substring[] = [$start, $length, $stringParser];

        return $this;
    }

    /**
     * @param string $encoding
     * @param string $exception
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function withEncoding(string $encoding, string $exception = 'Multibyte string does not appear to be of the requested encoding'): self
    {
        static $encodings = null;
        if($encodings === null) {
            $encodings = mb_list_encodings();
        }
        if(!in_array($encoding, $encodings)) {
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
            throw new ParsingException($value, $this->typeExceptionMessage, $path);
        }

        if($this->encoding) {
            [$encoding, $exception] = $this->encoding;
            if(!mb_check_encoding($value, $encoding)) {
                throw new ParsingException($value, $exception, $path);
            }
        } else {
            $encoding = mb_detect_encoding($value);
        }

        if ($this->length) {
            $this->length->parse(mb_strlen($value, $encoding), $path->meta('length'));
        }

        if ($this->substring) {
            /**
             * @var int $start
             * @var null|int $length
             * @var Parser $parser
             */
            foreach ($this->substring as [$start, $length, $parser]) {
                if ($value === '') {
                    $part = '';
                } else {
                    $part = (string)mb_substr($value, $start, $length, $encoding);
                }
                $parser->parse($part, $path->meta("$start:$length"));
            }
        }

        return $value;
    }
}