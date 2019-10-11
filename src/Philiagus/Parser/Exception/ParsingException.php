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

use Exception;
use Philiagus\Parser\Base\Path;
use Throwable;

/**
 * This exception is supposed to be thrown, when the value as provided by the input does not conform with the parser
 * e.g. when the value could not be converted to the target type or when the provided type is not of the correct type
 */
class ParsingException extends Exception
{

    /**
     * @var Path
     */
    private $path;

    /**
     * @var mixed
     */
    private $value;

    /**
     * ParsingException constructor.
     *
     * @param $value
     * @param string $message
     * @param Path $path
     * @param Throwable|null $previous
     */
    public function __construct($value, string $message, Path $path, Throwable $previous = null)
    {
        $this->value = $value;
        $this->path = $path;
        parent::__construct($message, 0, $previous);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getPath(): Path
    {
        return $this->path;
    }
}