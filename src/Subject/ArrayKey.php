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

class ArrayKey extends Subject
{
    public function __construct(Contract\Subject $sourceSubject, int|string $arrayKey)
    {
        parent::__construct($sourceSubject, (string)$arrayKey, $arrayKey, false, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return preg_match('/\s/', $this->description)
            ? " key " . var_export($this->description, true)
            : " key {$this->description}";
    }
}
