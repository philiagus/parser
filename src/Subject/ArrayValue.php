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

namespace Philiagus\Parser\Subject;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract;

/**
 * A subject representing the value assigned to an array key
 *
 * @package Subject
 */
class ArrayValue extends Subject
{
    public function __construct(
        Contract\Subject $sourceSubject,
        private readonly string|int $arrayKey,
        mixed $value
    )
    {
        parent::__construct($sourceSubject, (string)$arrayKey, $value, false, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return "[" . var_export($this->arrayKey, true) . "]";
    }
}
