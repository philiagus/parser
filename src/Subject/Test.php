<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Bittner <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Subject;

use Philiagus\Parser\Base\Subject;

class Test extends Subject
{

    public function __construct(
        Subject $subject,
        string $description,
        ?bool $isPathInValue = null,
        ?bool $throwOnError = null
    ) {
        parent::__construct(
            $subject->getValue(),
            $description,
            $subject,
            $isPathInValue ?? $subject->isPathInValue(),
            $throwOnError ?? $subject->throwOnError()
        );
    }

    protected function getPathStringPart(): string
    {
        return ' TEST: ' . $this->getPathDescription();
    }
}
