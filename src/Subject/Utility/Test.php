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

/**
 * A utility subject used to hand a value over to another parser for testing.
 *
 * Think of this subject as "if (another parser succeeds in testing this subject) then do something"
 *
 * @package Subject\Utility
 */
readonly class Test extends Subject
{

    public function __construct(
        Contract\Subject $subject,
        string           $description,
        bool             $forceThrowOnError = false
    )
    {
        parent::__construct($subject, $description, $subject->getValue(), true, $forceThrowOnError ?: null);
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return " ⁇{$this->description}⁇";
    }
}
