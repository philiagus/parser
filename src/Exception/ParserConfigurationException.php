<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Exception;

/**
 * This exception is supposed to be thrown when a Parser gets wrongly configured
 *
 * If the error in configuration is identified while the parser is being executed,
 * please throw a RuntimeParserConfigurationException instead
 *
 * @see RuntimeParserConfigurationException
 */
class ParserConfigurationException extends \LogicException
{
    /**
     * @param string $message
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
