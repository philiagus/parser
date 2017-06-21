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

namespace Philiagus\Parser\Exception;

use Philiagus\Parser\Base\Parser;
use Throwable;

/**
 * This exception is supposed to be thrown, when the value as provided by the input does not conform with the parser
 * e.g. when the value could not be converted to the target type or when the provided type is not of the correct type
 */
class ParsingException extends \Exception
{

    /**
     * @var string[]
     */
    private $path = [];

    /**
     * ParsingException constructor.
     *
     * @param string $message
     * @param string $path
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", string $path, Throwable $previous = null)
    {
        if ($path) {
            $this->path = explode(
                Parser::PATH_SEPARATOR,
                ltrim($path, Parser::PATH_SEPARATOR)
            );
        }
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string[]
     */
    public function getPath(): array
    {
        return $this->path;
    }

}