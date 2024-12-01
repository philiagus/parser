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
 * A subject representing the pair of a properties name and its value, represented as an array `[name, value]`
 *
 * @package Subject
 */
readonly class PropertyNameValuePair extends Subject
{
    public function __construct(Subject $source, string $propertyName, mixed $propertyValue)
    {
        parent::__construct($source, $propertyName, [$propertyName, $propertyValue], false, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return ' entry ' . var_export($this->description, true);
    }
}
