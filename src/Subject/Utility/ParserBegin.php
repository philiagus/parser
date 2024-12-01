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
 * A utility subject to represent that start of a parser
 *
 * @package Subject\Utility
 */
readonly class ParserBegin extends Subject
{

    public function __construct(Subject $original, string $description)
    {
        parent::__construct($original, $description, $original->getValue(), true, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return $this->description === '' ? '' : " ▷$this->description";
    }
}
