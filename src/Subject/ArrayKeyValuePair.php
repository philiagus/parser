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
 * A subject representing the combination of an array key and its assigned value in the shape `[key, value]`
 *
 * @package Subject
 */
readonly class ArrayKeyValuePair extends Subject
{
    public function __construct(Contract\Subject $sourceSubject, private readonly int|string $arrayKey, mixed $arrayValue)
    {
        parent::__construct($sourceSubject, (string)$arrayKey, [$arrayKey, $arrayValue], false, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return " entry " . var_export($this->arrayKey, true);
    }
}
