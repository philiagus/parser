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
use Philiagus\Parser\Contract;

class ParserBegin extends Subject
{

    public function __construct(Contract\Subject $originalSubject, string $description)
    {
        parent::__construct($originalSubject, $description, $originalSubject->getValue(), true, null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return $this->description === '' ? '' : " ▷$this->description";
    }
}
