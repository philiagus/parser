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

class PropertyValue extends Subject
{
    public function __construct(Subject $sourceSubject, string $propertyName, mixed $propertyValue)
    {
        parent::__construct($sourceSubject, $propertyName, $propertyValue, false, null);
    }

    /**
     * @param bool $isLastInChain *
     *
     * @inheritDoc
     */
    protected function getPathStringPart(bool $isLastInChain): string
    {
        return preg_match('/\s/', $this->getDescription())
            ? "[{$this->getDescription()}]"
            : ".{$this->getDescription()}";
    }
}
