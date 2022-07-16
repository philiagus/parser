<?php
/**
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

class Parser extends Subject
{

    public function __construct(Subject $parent, string $description)
    {
        parent::__construct(
            $parent->getValue(),
            $description,
            $parent,
            false,
            $parent->throwOnError()
        );
    }

    /**
     * @inheritDoc
     */
    protected function getPathStringPart(): string
    {
        if ($this->getPathDescription() === '') {
            return '';
        }

        return " parse: '{$this->getPathDescription()}'";
    }
}
