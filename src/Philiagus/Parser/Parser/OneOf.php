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
use Philiagus\Parser\Exception;
use Philiagus\Parser\Exception\ParsingException;

class OneOf extends Parser
{

    /**
     * @var string
     */
    private $exceptionMessage = 'Provided value does not match any of the expected formats';

    /**
     * @var Parser[]
     */
    private $options = [];

    /**
     * @var mixed[]
     */
    private $sameOptions = [];

    /**
     * @var mixed[]
     */
    private $equalsOptions = [];

    /**
     * Adds another potential parser the provided value might match
     *
     * @param Parser ...$parser
     *
     * @return $this
     */
    public function addOption(Parser ...$parser): self
    {
        $this->options = array_merge($this->options, $parser);

        return $this;
    }

    /**
     * Adds an option that is compared via == against the provided value
     *
     * @param mixed ...$options
     *
     * @return $this
     */
    public function addEqualsOption(...$options): self
    {
        $this->equalsOptions = array_merge($this->sameOptions, $options);

        return $this;
    }

    /**
     * Adds an option that is compared via === against the provided value
     *
     * @param mixed ...$options
     *
     * @return $this
     */
    public function addSameOption(...$options): self
    {
        $this->sameOptions = array_merge($this->sameOptions, $options);

        return $this;
    }

    /**
     * Defines the exception message to use if none of the provided parsers matches
     *
     * @param string $message
     *
     * @return $this
     */
    public function overwriteNonOfExceptionMessage(string $message): self
    {
        $this->exceptionMessage = $message;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (in_array($value, $this->sameOptions, true)) {
            return $value;
        }

        if (in_array($value, $this->equalsOptions)) {
            return $value;
        }

        $exceptions = [];
        foreach ($this->options as $option) {
            try {
                return $option->parse($value, $path);
            } catch (ParsingException $exception) {
                $exceptions[] = $exception;
            }
        }

        throw new Exception\OneOfParsingException(
            $value,
            $this->exceptionMessage,
            $path,
            $exceptions,
            $this->sameOptions,
            $this->equalsOptions
        );
    }
}