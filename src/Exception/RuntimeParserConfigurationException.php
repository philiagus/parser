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

namespace Philiagus\Parser\Exception;

/**
 * This exception is supposed to be thrown when a Parser gets wrongly configured and the
 * error in configuration is only identified during parser execution
 */
class RuntimeParserConfigurationException extends ParserConfigurationException
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
