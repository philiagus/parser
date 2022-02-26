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

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverridableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertSame implements Parser
{
    use Chainable, OverridableChainDescription;

    /** @var string */
    private string $exceptionMessage = 'The value is not the same as the expected value';

    /**
     * @var mixed
     */
    private $targetValue;

    private function __construct($value)
    {
        $this->targetValue = $value;
    }

    /**
     * @param mixed $value
     *
     * @return self
     */
    public static function value($value): self
    {
        return new self($value);
    }

    public function parse($value, ?Path $path = null)
    {
        if ($value !== $this->targetValue) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->exceptionMessage, ['value' => $value, 'expected' => $this->targetValue]),
                $path
            );
        }

        return $value;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert same', false);
    }
}
