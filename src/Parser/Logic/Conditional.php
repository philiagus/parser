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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\Utility\Forwarded;
use Philiagus\Parser\Subject\Utility\Test;
use Philiagus\Parser\Util\Debug;

class Conditional extends Base\Parser
{

    private const TYPE_SAME = 1,
        TYPE_EQUALS = 2,
        TYPE_SAME_LIST = 3,
        TYPE_EQUALS_LIST = 4,
        TYPE_PARSER = 5,
        TYPE_PARSER_PIPE = 6;


    /** @var array{int, mixed, Parser} */
    private array $elements = [];
    private bool $defaultSet = false;
    private mixed $default = null;
    private string $exceptionMessage = 'Provided value does not match any of the expected formats or values';

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
    public function ifSameAs($from, Parser $to): self
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
    public function ifSameAsListElement(array $froms, Parser $to): self
    {
        $this->elements[] = [self::TYPE_SAME_LIST, array_values($froms), $to];

        return $this;
    }

    /**
     * if the value is == $from the provided parser is called with the value
     *
     * @param mixed $from
     * @param Parser $to
     *
     * @return $this
     */
    public function ifEqualTo(mixed $from, Parser $to): self
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
    public function ifEqualToListElement(array $froms, Parser $to): self
    {

        $this->elements[] = [self::TYPE_EQUALS_LIST, array_values($froms), $to];

        return $this;
    }

    /**
     * TODO: Fix doc
     * Validates the value with $parser and on success calls $to with the value
     * If $pipe is true the value handed to $to is the result of $parser instead
     * of the unaltered value received by the parser
     *
     * @param Parser $parser
     * @param Parser $to
     *
     * @return $this
     */
    public function ifParser(Parser $parser, Parser $to): self
    {
        $this->elements[] = [self::TYPE_PARSER, $parser, $to];

        return $this;
    }

    /**
     * TODO: Fix doc
     * Validates the value with $parser and on success calls $to with the value
     * If $pipe is true the value handed to $to is the result of $parser instead
     * of the unaltered value received by the parser
     *
     * @param Parser $parser
     * @param Parser $to
     *
     * @return $this
     */
    public function ifParserPiped(Parser $parser, Parser $to): self
    {
        $this->elements[] = [self::TYPE_PARSER_PIPE, $parser, $to];

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

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        $errors = [];
        /**
         * @type Parser $to
         */
        foreach ($this->elements as [$type, $from, $to]) {
            switch ($type) {
                case self::TYPE_SAME:
                    if ($value === $from) {
                        return $builder->createResultFromResult(
                            $to->parse(new Forwarded($builder->getSubject(), 'same found'))
                        );
                    }
                    $errors[] = new Error(
                        $builder->getSubject(),
                        'Value is not same as ' . Debug::stringify($from)
                    );
                    break;
                case self::TYPE_SAME_LIST:
                    if (@in_array($value, $from, true)) {
                        return $builder->createResultFromResult($to->parse(new Forwarded($builder->getSubject(), 'same found in list')));
                    }
                    $errors[] = new Error(
                        $builder->getSubject(),
                        'Value is not same as ' . implode(', ', array_map(fn($value) => Debug::stringify($value), $from))
                    );
                    break;
                case self::TYPE_EQUALS:
                    if ($value == $from) {
                        return $builder->createResultFromResult($to->parse(new Forwarded($builder->getSubject(), 'equal found')));
                    }
                    $errors[] = new Error(
                        $builder->getSubject(),
                        'Value is not equal to ' . Debug::stringify($from)
                    );
                    break;
                case self::TYPE_EQUALS_LIST:
                    // @ suppresses errors when comparing objects to scalars
                    if (@in_array($value, $from)) {
                        return $builder->createResultFromResult(
                            $to->parse(new Forwarded($builder->getSubject(), 'equal found in list'))
                        );
                    }
                    $errors[] = new Error(
                        $builder->getSubject(),
                        'Value is not equal to ' . implode(', ', array_map(fn($value) => Debug::stringify($value), $from))
                    );
                    break;
                case self::TYPE_PARSER:
                    $childErrors = null;
                    /** @var Parser $from */
                    try {
                        $parserResult = $from->parse(new Test($builder->getSubject(), 'check', true));

                        if (!$parserResult->isSuccess()) {
                            $childErrors = $parserResult->getErrors();
                        }
                    } catch (Exception\ParsingException $e) {
                        $childErrors = [$e->getError()];
                    }

                    if ($childErrors === null) {
                        return $builder->createResultFromResult(
                            $to->parse(
                                new Forwarded($builder->getSubject(), 'parser check')
                            )
                        );
                    }
                    $errors[] = new Error($builder->getSubject(), 'Value did not match parser', sourceErrors: $childErrors);
                    unset($childErrors);
                    break;
                case self::TYPE_PARSER_PIPE:
                    $childErrors = null;
                    /** @var Parser $from */
                    try {
                        $parserResult = $from->parse(new Test($builder->getSubject(), 'check for pipe'));
                        if (!$parserResult->isSuccess()) {
                            $childErrors = $parserResult->getErrors();
                        }
                    } catch (Exception\ParsingException $e) {
                        $childErrors = [$e->getError()];
                    }
                    if ($childErrors === null && isset($parserResult)) {
                        return $builder->createResultFromResult(
                            $to->parse($parserResult)
                        );
                    }
                    $errors[] = new Error($builder->getSubject(), 'Value did not match parser', sourceErrors: $childErrors);
                    unset($childErrors);
                    unset($parserResult);
                    break;
            }
        }

        if ($this->defaultSet) {
            return $builder->createResult($this->default);
        }

        $builder->logErrorUsingDebug($this->exceptionMessage, sourceErrors: $errors);

        return $builder->createResultUnchanged();
    }

    protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'Map';
    }
}
