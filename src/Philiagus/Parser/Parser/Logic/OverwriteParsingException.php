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

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\ChainableParser;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class OverwriteParsingException implements ChainableParser
{
    use Chainable;

    /** @var string */
    private string $message;

    /** @var Parser */
    private Parser $parser;

    private function __construct(string $message, Parser $parser)
    {
        $this->message = $message;
        $this->parser = $parser;
    }

    public static function withMessage(string $message, Parser $around)
    {
        return new self($message, $around);
    }

    public function parse($value, Path $path = null)
    {
        try {
            return $this->parser->parse($value, $path);
        } catch (ParsingException $exception) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->message, ['value' => $value]),
                $path,
                $exception
            );
        }
    }
}
