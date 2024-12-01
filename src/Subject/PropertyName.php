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
 * A subject representing the name of an object property
 *
 * @package Subject
 */
readonly class PropertyName extends Subject
{

    public function __construct(Subject $source, string $propertyName)
    {
        parent::__construct($source, $propertyName, $propertyName, false, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return " property name " . var_export($this->description, true);
    }
}
