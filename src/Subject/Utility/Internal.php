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

namespace Philiagus\Parser\Subject\Utility;

use Philiagus\Parser\Base\Subject;

/**
 * A utility subject used when a value is internally changed by a parser to represent that
 * value change (such as when the ParseArray parser changes the array it internally creates an
 * instance of this subject)
 *
 * @package Subject\Utility
 */
readonly class Internal extends Subject
{

    public function __construct(Subject $source, string $description, mixed $value)
    {
        parent::__construct($source, $description, $value, true, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return " {$this->description}↩";
    }
}
