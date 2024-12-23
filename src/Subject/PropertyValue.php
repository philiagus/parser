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

/**
 * A subject representing the value of an object property.
 *
 * This subject always contains a string
 *
 * @package Subject
 */
readonly class PropertyValue extends Subject
{
    public function __construct(Subject $source, string $propertyName, mixed $propertyValue)
    {
        parent::__construct($source, $propertyName, $propertyValue, false, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return preg_match('/\W/', $this->description)
            ? "[$this->description]"
            : ".$this->description";
    }
}
