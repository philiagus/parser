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
 * A subject representing the value of an object property.
 *
 * This subject always contains a string
 *
 * @package Subject
 */
class PropertyValue extends Subject
{
    public function __construct(Contract\Subject $sourceSubject, string $propertyName, mixed $propertyValue)
    {
        parent::__construct($sourceSubject, $propertyName, $propertyValue, false, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return preg_match('/\W/', $this->description)
            ? "[$this->description]"
            : ".$this->description";
    }
}
