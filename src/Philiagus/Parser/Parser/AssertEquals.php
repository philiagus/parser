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
use Philiagus\Parser\Util\Debug;

class AssertEquals
    extends Parser
{

    private const DEFAULT_MESSAGE = 'The value is not equal to the expected value';

    /**
     * @var string|null
     */
    private $exceptionMessage = null;

    /**
     * @var mixed
     */
    private $targetValue;

    /**
     * Shortcut constructor for assertion against a value when no by-reference check is needed
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The value the received value is compared against
     *
     * @param $value
     * @param string $exceptionMessage
     *
     * @return static
     *
     * @see Debug::parseMessage()
     *
     */
    public static function value($value, string $exceptionMessage = self::DEFAULT_MESSAGE): self
    {
        $instance = new self();
        $instance->targetValue = $value;
        $instance->exceptionMessage = $exceptionMessage;

        return $instance;
    }

    /**
     * Sets the value to be == compared against
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The value the received value is compared against
     *
     * @param $equalsValue
     * @param string $exceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function setValue($equalsValue, string $exceptionMessage = self::DEFAULT_MESSAGE): self
    {
        $this->targetValue = $equalsValue;
        $this->exceptionMessage = $exceptionMessage;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if ($this->exceptionMessage === null) {
            throw new ParserConfigurationException('Called AssertEquals parser without a value set');
        }

        if ($value != $this->targetValue) {

            throw new ParsingException(
                $value,
                Debug::parseMessage(
                    $this->exceptionMessage,
                    [
                        'value' => $value,
                        'expected' => $this->targetValue,
                    ]
                ),
                $path
            );
        }

        return $value;
    }
}