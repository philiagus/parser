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


namespace Philiagus\Parser\Base;

use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;

trait OverwritableParserDescription
{

    private string $overwritableChainDescriptionMessage;

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setChainDescription(string $description): self
    {
        $this->overwritableChainDescriptionMessage = $description;

        return $this;
    }

    /**
     * @param Subject $subject
     *
     * @return ResultBuilder
     */
    public function createResultBuilder(Subject $subject): ResultBuilder
    {
        return $subject->getResultBuilder(
            $this->overwritableChainDescriptionMessage ?? $this->getDefaultChainDescription($subject)
        );
    }

    /**
     * @param Subject $subject
     *
     * @return string
     */
    abstract protected function getDefaultChainDescription(Subject $subject): string;

}