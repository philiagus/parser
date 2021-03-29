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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

/**
 * Class BooleanPrimitive
 *
 * @package Philiagus\Parser
 */
class AssertBoolean
    extends Parser
{

    private $typeExceptionMessage = 'Provided value is not a boolean';

    /**
     * Sets the exception message sent when the input value is not a bool
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
    public function setTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }


    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (is_bool($value)) return $value;

        throw new ParsingException(
            $value,
            Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
            $path
        );

    }
}