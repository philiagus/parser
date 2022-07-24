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

class ArrayKey extends Subject
{
    public function __construct(Subject $sourceSubject, int|string $arrayKey)
    {
        parent::__construct($sourceSubject, (string) $arrayKey, $arrayKey, false, null);
    }

    /**
     * @inheritDoc
     */
    protected function getPathStringPart(): string
    {
        return " key " . var_export($this->description, true);
    }
}
