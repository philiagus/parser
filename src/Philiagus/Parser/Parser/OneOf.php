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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class OneOf extends Parser
{

    /**
     * @var string
     */
    private $exceptionMessage = 'Provided value does not match any of the expected formats or values';

    /**
     * @var ParserContract[]
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
     * @var bool
     */
    private $defaultSet = false;

    /**
     * @var mixed
     */
    private $default = null;

    /**
     * Adds another potential parser the provided value might match
     *
     * @param ParserContract ...$parser
     *
     * @return $this
     */
    public function addOption(ParserContract ...$parser): self
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
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function overwriteNonOfExceptionMessage(string $message): self
    {
        $this->exceptionMessage = $message;

        return $this;
    }

    /**
     * Defines a default to be returned if none of the provided options match
     * @param $value
     *
     * @return $this
     * @throws Exception\ParserConfigurationException
     */
    public function setDefaultResult($value): self
    {
        if ($this->defaultSet) {
            throw new Exception\ParserConfigurationException(
                'The default for OneOf was already set and cannot be overwritten'
            );
        }

        $this->defaultSet = true;
        $this->default = $value;

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

        if ($this->defaultSet) {
            return $this->default;
        }

        throw new Exception\OneOfParsingException(
            $value,
            Debug::parseMessage($this->exceptionMessage, ['value' => $value]),
            $path,
            $exceptions,
            $this->sameOptions,
            $this->equalsOptions
        );
    }
}