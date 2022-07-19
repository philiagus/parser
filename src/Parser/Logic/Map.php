<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class Map implements Parser
{
    use Chainable, OverwritableParserDescription;

    private const TYPE_SAME = 1;
    private const TYPE_EQUALS = 2;
    private const TYPE_SAME_LIST = 3;
    private const TYPE_EQUALS_LIST = 4;
    private const TYPE_PARSER = 5;
    private const TYPE_PARSER_PIPE = 6;

    /** @var string */
    private string $exceptionMessage = 'Provided value does not match any of the expected formats or values';

    /** @var array */
    private array $elements = [];

    /** @var bool */
    private bool $defaultSet = false;

    private $default = null;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * Defines the exception message to use if none of the provided parsers matches
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
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
     *
     * @param $from
     * @param Parser $to
     *
     * @return $this
     */
    public function addSame($from, Parser $to): self
    {
        $this->elements[] = [self::TYPE_SAME, $from, $to];

        return $this;
    }

    /**
     * If the value provided is the same as one of the values in $froms the defined
     * parser is called with the value
     *
     * @param array $froms
     * @param Parser $to
     *
     * @return $this
     */
    public function addSameList(array $froms, Parser $to): self
    {
        $this->elements[] = [self::TYPE_SAME_LIST, array_values($froms), $to];

        return $this;
    }

    /**
     * if the value is == $from the provided parser is called with the value
     *
     * @param $from
     * @param Parser $to
     *
     * @return $this
     */
    public function addEquals($from, Parser $to): self
    {

        $this->elements[] = [self::TYPE_EQUALS, $from, $to];

        return $this;
    }

    /**
     * If $froms contains something == to the provided value the parser is called
     * with the value
     *
     * @param array $froms
     * @param Parser $to
     *
     * @return $this
     */
    public function addEqualsList(array $froms, Parser $to): self
    {

        $this->elements[] = [self::TYPE_EQUALS_LIST, array_values($froms), $to];

        return $this;
    }

    /**
     * Validates the value with $parser and on success calls $to with the value
     * If $pipe is true the value handed to $to is the result of $parser instead
     * of the unaltered value received by the parser
     *
     * @param Parser $parser
     * @param Parser $to
     * @param bool $pipe
     *
     * @return $this
     */
    public function addParser(Parser $parser, Parser $to, bool $pipe = false): self
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


    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        $value = $subject->getValue();
        $errors = [];
        /**
         * @type Parser $to
         */
        foreach ($this->elements as [$type, $from, $to]) {
            switch ($type) {
                case self::TYPE_SAME:
                    if ($value === $from) {
                        return $builder->createResultFromResult($to->parse($builder->subjectForwarded('same')));
                    }
                    $errors[] = new Error(
                        $subject,
                        'Value is not same as ' . Debug::stringify($from)
                    );
                    break;
                case self::TYPE_SAME_LIST:
                    if (in_array($value, $from, true)) {
                        return $builder->createResultFromResult($to->parse($builder->subjectForwarded('same list')));
                    }
                    $errors[] = new Error(
                        $subject,
                        'Value is not same as ' . implode(', ', array_map(fn($value) => Debug::stringify($value), $from))
                    );
                    break;
                case self::TYPE_EQUALS:
                    if ($value == $from) {
                        return $builder->createResultFromResult($to->parse($builder->subjectForwarded('equals')));
                    }
                    $errors[] = new Error(
                        $subject,
                        'Value is not equal to ' . Debug::stringify($from)
                    );
                    break;
                case self::TYPE_EQUALS_LIST:
                    if (in_array($value, $from)) {
                        return $builder->createResultFromResult($to->parse($builder->subjectForwarded('equals list')));
                    }
                    $errors[] = new Error(
                        $subject,
                        'Value is not equal to ' . implode(', ', array_map(fn($value) => Debug::stringify($value), $from))
                    );
                    break;
                case self::TYPE_PARSER:
                    /** @var Parser $from */
                    try {
                        $builder->incorporateResult(
                            $from->parse(
                                $builder->subjectMeta('check', $subject->getValue(), true)
                            )
                        );
                    } catch (Exception\ParsingException $e) {
                        $errors[] = $e->getError();
                        break;
                    }

                    return $builder->createResultFromResult($to->parse($builder->subjectForwarded('parser check')));
                case self::TYPE_PARSER_PIPE:
                    /** @var Parser $from */
                    try {
                        $parserResult = $from->parse($builder->subjectForwarded('check with pipe'));
                    } catch (Exception\ParsingException $e) {
                        $errors[] = $e->getError();
                        break;
                    }

                    return $builder->createResultFromResult(
                        $to->parse($parserResult->subjectChain())
                    );
            }
        }

        if ($this->defaultSet) {
            return $this->default;
        }

        $builder->logErrorUsingDebug(
            $this->exceptionMessage,
            [], null,
            $errors
        );

        return $builder->createResultUnchanged();
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'Map';
    }
}
