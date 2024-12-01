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
use Philiagus\Parser\Base\Subject\Memory;

readonly class SubjectMock extends Subject
{

    public function __construct(
        ?Subject                $source = null,
        string                  $description = 'description',
        mixed                   $value = 'value',
        bool                    $isUtility = true,
        ?bool                   $throwOnError = null,
        private ?string         $path = null,
        private ?Subject\Memory $fullMemory = null,
    )
    {
        parent::__construct($source, $description, $value, $isUtility, $throwOnError);
    }

    public function getFullMemory(): Memory
    {
        return $this->fullMemory ?? parent::getFullMemory();
    }

    protected function getPathStringPart(bool $isLastInChain): string
    {
        if ($this->path !== null)
            return $this->path;

        if ($isLastInChain)
            return ' <mock last>';

        return ' <mock>';
    }
}

