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
     * Adds another potential parser the provided value might match
     *
     * @param Parser $parser
     *
     * @return $this
     */
    public function addOption(Parser $parser): self
    {
        $this->options[] = $parser;

        return $this;
    }

    /**
     * Adds an option that is compared via == against the provided value
     * @param mixed $option
     *
     * @return $this
     */
    public function addEqualsOption($option): self
    {
        $this->options[] = (new AssertEquals())->withValue($option);

        return $this;
    }

    /**
     * Adds an option that is compared via === against the provided value
     * @param mixed $option
     *
     * @return $this
     */
    public function addSameOption($option): self
    {
        $this->options[] = (new AssertSame())->withValue($option);

        return $this;
    }

    /**
     * Defines the exception message to use if none of the provided parsers matches
     *
     * @param string $message
     *
     * @return $this
     */
    public function withNonOfExceptionMessage(string $message): self
    {
        $this->exceptionMessage = $message;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (empty($this->options)) {
            throw new Exception\ParserConfigurationException('OneOf parser was not provided with any options');
        }

        $exceptions = [];

        /** @var Parser $option */
        foreach ($this->options as $option) {
            try {
                return $option->parse($value, $path);
            } catch (ParsingException $exception) {
                $exceptions[] = $exception;
            }
        }

        throw new Exception\MultipleParsingException(
            $value,
            $this->exceptionMessage,
            $path,
            $exceptions
        );
    }
}