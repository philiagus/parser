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

class Internal extends Subject
{

    public function __construct(Subject $sourceSubject, string $description, mixed $value)
    {
        parent::__construct($sourceSubject, $description, $value, true, null);
    }

    /**
     * @inheritDoc
     */
    protected function getPathStringPart(): string
    {
        return "[{$this->description}]";
    }
}
