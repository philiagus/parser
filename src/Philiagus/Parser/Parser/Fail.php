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

class Fail extends Parser
{
    /**
     * @var string
     */
    private $message = 'This value can never match';

    /**
     * Sets the exception message to be thrown
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     */
    public function overwriteExceptionMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Static constructor to shorthand setting a specific message
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return static
     */
    public static function message(string $message): self
    {
        return (new self())->overwriteExceptionMessage($message);
    }

    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        throw new ParsingException(
            $value,
            Debug::parseMessage(
                $this->message,
                ['value' => $value]
            ),
            $path
        );
    }
}