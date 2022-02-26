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
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class OneOf implements Parser
{
    use Chainable, OverwritableChainDescription;

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
    private $default = null;

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
        $this->options = array_merge($this->options, $parser);

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
        $this->sameOptions = array_merge($this->sameOptions, $options);

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
        $this->equalsOptions = array_merge($this->sameOptions, $options);

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

    public function parse($value, Path $path = null)
    {
        $path ??= Path::default($value);
        if (in_array($value, $this->sameOptions, true)) {
            return $value;
        }

        if (in_array($value, $this->equalsOptions)) {
            return $value;
        }

        $exceptions = [];
        foreach ($this->options as $index => $option) {
            try {
                return $option->parse($value, $path->chain("option #$index", false));
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

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('OneOf', false);
    }
}
