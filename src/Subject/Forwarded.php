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

class Forwarded extends Subject
{

    public function __construct(Subject $parent, string $description, ?bool $throwOnError = null)
    {
        parent::__construct(
            $parent->getValue(),
            $description,
            $parent,
            false,
            $throwOnError ?? $parent->throwOnError()
        );
    }


    protected function getPathStringPart(): string
    {
        return ' -' . $this->getPathDescription() . '->';
    }
}