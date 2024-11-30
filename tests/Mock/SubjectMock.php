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

namespace Philiagus\Parser\Test\Mock;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract;

readonly class SubjectMock extends Subject {

    public function __construct(
        ?Contract\Subject $sourceSubject = null,
        string $description = 'description',
        mixed $value = 'value',
        bool $isUtilitySubject = true,
        ?bool $throwOnError = null
    )
    {
        parent::__construct($sourceSubject, $description, $value, $isUtilitySubject, $throwOnError);
    }

    protected function getPathStringPart(bool $isLastInChain): string
    {
        if($isLastInChain)
            return ' <mock last>';
        return ' <mock>';
    }
}

