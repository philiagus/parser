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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class OneOf implements Parser
{
    use Chainable, OverwritableParserDescription;

    /** @var string */
    private string $exceptionMessage = 'Provided value does not match any of the expected formats or values';

    /** @var Parser[] */
    private array $options = [];

    /** @var array */
    private array $sameOptions = [];

    /** @var array */
    private array $equalsOptions = [];

    /** @var bool */
    private bool $defaultSet = false;

    /** @var mixed */
    private mixed $default = null;

    private function __construct()
    {
    }

    public static function nullOr(Parser $parser): self
    {
        return self::new()
            ->sameAs(null)
            ->parser($parser);
    }

    /**
     * Adds another potential parser the provided value might match
     *
     * @param Parser ...$parser
     *
     * @return $this
     */
    public function parser(Parser ...$parser): self
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
    public function sameAs(...$options): self
    {
        $this->sameOptions = [...$this->sameOptions, ...$options];

        return $this;
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * Adds an option that is compared via == against the provided value
     *
     * @param mixed ...$options
     *
     * @return $this
     */
    public function equalTo(...$options): self
    {
        $this->equalsOptions = [...$this->sameOptions, ...$options];

        return $this;
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

        /** @var Error[] $errors */
        $errors = [];
        if (!empty($this->sameOptions)) {
            if (in_array($value, $this->sameOptions, true)) {
                return $builder->createResultUnchanged();
            }

            $errors[] = new Error(
                $subject,
                'Value is not same as any of these: ' . implode(', ', array_map(fn($value) => Debug::stringify($value), $this->sameOptions))
            );
        }

        if (!empty($this->equalsOptions)) {
            if (in_array($value, $this->equalsOptions)) {
                return $builder->createResultUnchanged();
            }

            $errors[] = new Error(
                $subject,
                'Value is not equal to any of these: ' . implode(', ', array_map(fn($value) => Debug::stringify($value), $this->sameOptions))
            );
        }

        foreach ($this->options as $index => $option) {
            try {
                $result = $option->parse(
                    $builder->subjectForwarded("one of parser #$index")
                );
            } catch (ParsingException $exception) {
                $errors[] = $exception->getError();
                continue;
            }

            if ($result->isSuccess()) {
                return $builder->createResultFromResult($result);
            }
            $errors = [...$errors, ...$result->getErrors()];
        }

        if ($this->defaultSet) {
            return $builder->createResult($this->default);
        }

        $builder->logErrorUsingDebug($this->exceptionMessage, [], null, $errors);

        return $builder->createResultUnchanged();
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'OneOf';
    }
}
