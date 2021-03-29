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
use Philiagus\Parser\Exception;
use Philiagus\Parser\Util\Debug;

class Map extends Parser
{
    private const TYPE_SAME = 1;
    private const TYPE_EQUALS = 2;
    private const TYPE_SAME_LIST = 3;
    private const TYPE_EQUALS_LIST = 4;
    private const TYPE_PARSER = 5;
    private const TYPE_PARSER_PIPE = 6;

    /**
     * @var string
     */
    private $exceptionMessage = 'Provided value does not match any of the expected formats or values';

    /**
     * @var array
     */
    private $elements = [];

    /**
     * @var bool
     */
    private $defaultSet = false;

    /**
     * @var mixed
     */
    private $default = null;

    /**
     * Defines the exception message to use if none of the provided parsers matches
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
    public function setNonOfExceptionMessage(string $message): self
    {
        $this->exceptionMessage = $message;

        return $this;
    }

    /**
     * Compares the value === $from and on success calls the defined parser with
     * the value
     * @param $from
     * @param ParserContract $to
     *
     * @return $this
     */
    public function addSame($from, ParserContract $to): self
    {
        $this->elements[] = [self::TYPE_SAME, $from, $to];

        return $this;
    }

    /**
     * If the value provided is the same as one of the values in $froms the defined
     * parser is called with the value
     * @param array $froms
     * @param ParserContract $to
     *
     * @return $this
     */
    public function addSameList(array $froms, ParserContract $to): self
    {
        $this->elements[] = [self::TYPE_SAME_LIST, array_values($froms), $to];

        return $this;
    }

    /**
     * if the value is == $from the provided parser is called with the value
     * @param $from
     * @param ParserContract $to
     *
     * @return $this
     */
    public function addEquals($from, ParserContract $to): self
    {

        $this->elements[] = [self::TYPE_EQUALS, $from, $to];

        return $this;
    }

    /**
     * If $froms contains something == to the provided value the parser is called
     * with the value
     * @param array $froms
     * @param ParserContract $to
     *
     * @return $this
     */
    public function addEqualsList(array $froms, ParserContract $to): self
    {

        $this->elements[] = [self::TYPE_EQUALS_LIST, array_values($froms), $to];

        return $this;
    }

    /**
     * Validates the value with $parser and on success calls $to with the value
     * If $pipe is true the value handed to $to is the result of $parser instead
     * of the unaltered value received by the parser
     *
     * @param ParserContract $parser
     * @param ParserContract $to
     * @param bool $pipe
     *
     * @return $this
     */
    public function addParser(ParserContract $parser, ParserContract $to, bool $pipe = false): self
    {
        if ($pipe) {
            $this->elements[] = [self::TYPE_PARSER_PIPE, $parser, $to];
        } else {
            $this->elements[] = [self::TYPE_PARSER, $parser, $to];
        }

        return $this;
    }

    /**
     * Defines a default to be returned if none of the provided options match
     *
     * @param $value
     *
     * @return $this
     */
    public function setDefaultResult($value): self
    {
        $this->defaultSet = true;
        $this->default = $value;

        return $this;
    }


    protected function execute($value, Path $path)
    {
        $exceptions = [];
        $sameOptions = [];
        $equalsOptions = [];

        /**
         * @type ParserContract $to
         */
        foreach ($this->elements as [$type, $from, $to]) {
            switch ($type) {
                case self::TYPE_SAME:
                    if ($value === $from) {
                        return $to->parse($value, $path);
                    }
                    $sameOptions[] = $from;
                    break;
                case self::TYPE_SAME_LIST:
                    if (in_array($value, $from, true)) {
                        return $to->parse($value, $path);
                    }
                    $sameOptions = array_merge($sameOptions, $from);
                    break;
                case self::TYPE_EQUALS:
                    if ($value == $from) {
                        return $to->parse($value, $path);
                    }
                    $equalsOptions[] = $from;
                    break;
                case self::TYPE_EQUALS_LIST:
                    if (in_array($value, $from)) {
                        return $to->parse($value, $path);
                    }
                    $equalsOptions = array_merge($equalsOptions, $from);
                    break;
                case self::TYPE_PARSER:
                    /** @var ParserContract $from */
                    try {
                        $from->parse($value, $path);
                    } catch (Exception\ParsingException $e) {
                        $exceptions[] = $e;
                        break;
                    }

                    return $to->parse($value, $path);
                case self::TYPE_PARSER_PIPE:
                    /** @var ParserContract $from */
                    try {
                        $parserResult = $from->parse($value, $path);
                    } catch (Exception\ParsingException $e) {
                        $exceptions[] = $e;
                        break;
                    }

                    return $to->parse($parserResult, $path);
            }
        }

        if($this->defaultSet) {
            return $this->default;
        }

        throw new Exception\OneOfParsingException(
            $value,
            Debug::parseMessage($this->exceptionMessage, ['value' => $value]),
            $path,
            $exceptions,
            $sameOptions,
            $equalsOptions
        );
    }
}