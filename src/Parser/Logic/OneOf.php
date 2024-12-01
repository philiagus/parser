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

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Utility\Forwarded;
use Philiagus\Parser\Util\Stringify;

/**
 * Checks that the value provided matches one of the provided values or parsers
 *
 * Please be aware that these values are not evaluated in order. For performance reasons the same and equal
 * values are accumulated and compared before the list of parsers are checked.
 *
 * @package Parser\Logic
 */
class OneOf extends Base\Parser
{
    private string $errorMessage = 'Provided value does not match any of the expected formats or values';

    /** @var Parser[] */
    private array $options = [];
    private array $sameOptions = [];
    private array $equalsOptions = [];

    /** @var bool */
    private bool $defaultSet = false;
    private mixed $default = null;

    protected function __construct()
    {
    }

    /**
     * Shortcut to create a OneOf parser that allows for a NULL value or the provided parser value
     * This is purely syntactical sugar to easily allow for nullable values
     *
     * @param Parser $parser
     *
     * @return static
     */
    public static function nullOr(Parser $parser): static
    {
        return static::new()
            ->sameAs(null)
            ->parser($parser);
    }

    /**
     * Adds another option the provided value could match
     *
     * @param Parser ...$parser
     *
     * @return $this
     */
    public function parser(Parser ...$parser): static
    {
        $this->options = [...$this->options, ...$parser];

        return $this;
    }

    /**
     * Adds an option that is compared via === against the provided value
     *
     * @param mixed ...$options
     *
     * @return $this
     */
    public function sameAs(...$options): static
    {
        $this->sameOptions = [...$this->sameOptions, ...$options];

        return $this;
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Adds an option that is compared via == against the provided value
     *
     * @param mixed ...$options
     *
     * @return $this
     */
    public function equalTo(...$options): static
    {
        $this->equalsOptions = [...$this->sameOptions, ...$options];

        return $this;
    }

    /**
     * Defines the error message to use if none of the provided parsers matches
     *
     * The error message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Stringify::parseMessage()
     *
     */
    public function setNonOfErrorMessage(string $message): static
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * Defines a default value to be returned if none of the provided options matches or results in a success
     *
     * @param $value
     *
     * @return $this
     */
    public function setDefaultResult($value): static
    {
        $this->defaultSet = true;
        $this->default = $value;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        $subject = $builder->getSubject();

        /** @var Error[] $errors */
        $errors = [];
        if (!empty($this->sameOptions)) {
            if (in_array($value, $this->sameOptions, true)) {
                return $builder->createResultUnchanged();
            }

            $errors[] = new Error($subject, 'Value is not same as any of the provided values');
        }

        if (!empty($this->equalsOptions)) {
            if (in_array($value, $this->equalsOptions)) {
                return $builder->createResultUnchanged();
            }

            $errors[] = new Error($subject, 'Value is not equal to any of the provided values');
        }

        foreach ($this->options as $index => $option) {
            $forwardSubject = new Forwarded($builder->getSubject(), "OneOf parser #$index");
            try {
                $result = $option->parse($forwardSubject);
                if ($result->isSuccess()) {
                    return $builder->createResultFromResult($result);
                }
                $childErrors = $result->getErrors();
            } catch (ParsingException $exception) {
                $childErrors = [$exception->getError()];
            }
            $errors[] = new Error($forwardSubject, 'Value could not be parsed', sourceErrors: $childErrors);
            unset($forwardSubject);
        }

        if ($this->defaultSet) {
            return $builder->createResult($this->default);
        }

        $builder->logErrorStringify($this->errorMessage, [], null, $errors);

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'OneOf';
    }
}
