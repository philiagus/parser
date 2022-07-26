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

namespace Philiagus\Parser\Subject\Utility;

use Philiagus\Parser\Base\Subject;

class Forwarded extends Subject
{

    public function __construct(\Philiagus\Parser\Contract\Subject $subject, string $description)
    {
        parent::__construct($subject, $description, $subject->getValue(), true, null);
    }


    protected function getPathStringPart(bool $isLastInChain): string
    {
        return ' ⇒' . $this->getDescription() . '⇒';
    }

}
