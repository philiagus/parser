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
 * A subject representing meta information of a value, such as the encoding of a string,
 * the number of elements in an array or the class of an object.
 *
 * @package Subject
 */
readonly class MetaInformation extends Subject
{

    public function __construct(Subject $source, string $description, mixed $value)
    {
        parent::__construct($source, $description, $value, false, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return " $this->description";
    }
}
