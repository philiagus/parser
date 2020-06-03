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

class AssertSame
    extends Parser
{

    private const DEFAULT_MESSAGE = 'The value is not the same as the expected value';

    /**
     * @var null|string
     */
    private $exceptionMessage = null;

    /**
     * @var mixed
     */
    private $targetValue;

    /**
     * Shortcut constructor for assertion against a value when no by-reference check is needed
     *
     * @param $value
     * @param string $exceptionMessage
     *
     * @return static
     */
    public static function value($value, string $exceptionMessage = self::DEFAULT_MESSAGE): self
    {
        $instance = new self();
        $instance->targetValue = $value;
        $instance->exceptionMessage = $exceptionMessage;

        return $instance;
    }

    /**
     * Sets the value to be === compared against
     *
     * @param $equalsValue
     * @param string $exceptionMessage
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setValue($equalsValue, string $exceptionMessage = self::DEFAULT_MESSAGE): self
    {
        if ($this->exceptionMessage !== null) {
            throw new ParserConfigurationException(
                'Tried to overwrite value of configured same parser'
            );
        }
        $this->exceptionMessage = $exceptionMessage;
        $this->targetValue = $equalsValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if ($this->exceptionMessage === null) {
            throw new ParserConfigurationException('Called AssertSame parse without a value set');
        }

        if ($value !== $this->targetValue) {
            throw new ParsingException($value, $this->exceptionMessage, $path);
        }

        return $value;
    }
}