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

class ParserBegin extends Subject
{

    public function __construct(\Philiagus\Parser\Contract\Subject $originalSubject, string $description)
    {
        parent::__construct($originalSubject, $description, $originalSubject->getValue(), true, null);
    }

    /**
     * @param bool $isLastInChain *
     *
     * @inheritDoc
     */
    protected function getPathStringPart(bool $isLastInChain): string
    {
        return $this->getDescription() === '' ? '' : " ▷{$this->getDescription()}";
    }
}
