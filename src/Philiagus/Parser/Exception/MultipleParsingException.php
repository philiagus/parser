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

class MultipleParsingException extends ParsingException
{

    /** @var ParsingException[] */
    private array $parsingExceptions;

    /**
     * MultipleParsingException constructor.
     *
     * @param $value
     * @param string $message
     * @param Path $path
     * @param ParsingException[] $parsingExceptions
     */
    public function __construct($value, string $message, Path $path, array $parsingExceptions = [])
    {
        parent::__construct($value, $message, $path);
        foreach ($parsingExceptions as $parsingException) {
            if (!$parsingException instanceof ParsingException) {
                throw new \LogicException(
                    'MultipleParsingException was created with non-ParsingException in provided list of Exceptions'
                );
            }
        }
        $this->parsingExceptions = $parsingExceptions;
    }

    /**
     * @return ParsingException[]
     */
    public function getParsingExceptions(): array
    {
        return $this->parsingExceptions;
    }

}
