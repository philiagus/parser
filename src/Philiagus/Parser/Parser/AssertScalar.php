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

class AssertScalar extends Parser
{

    private $exceptionMessage = 'Provided value is not scalar';

    /**
     * Defines the exception message to be thrown if the value is not scalar
     *
     * @param string $message
     *
     * @return $this
     */
    public function overwriteExceptionMessage(string $message): self
    {
        $this->exceptionMessage = $message;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (is_scalar($value)) return $value;

        throw new ParsingException($value, $this->exceptionMessage, $path);
    }
}