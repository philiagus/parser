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

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Parser\Logic\OverwriteParsingException;

/**
 * This exception is supposed to be thrown, when the value as provided by the input does not conform with the parser
 * e.g. when the value could not be converted to the target type or when the provided type is not of the correct type
 */
class ParsingException extends \Exception
{
    private Path $path;

    private $value;

    /**
     * ParsingException constructor.
     *
     * @param $value
     * @param string $message
     * @param Path|null $path
     * @param \Throwable|null $previous
     */
    public function __construct($value, string $message, ?Path $path, \Throwable $previous = null)
    {
        $this->value = $value;
        $this->path = $path ?? Path::default($value);
        parent::__construct($message, 0, $previous);
    }

    public static function overwriteAround(string $message, Parser $around): OverwriteParsingException
    {
        return OverwriteParsingException::withMessage($message, $around);
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
