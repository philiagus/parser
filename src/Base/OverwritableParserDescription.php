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

use Philiagus\Parser\ResultBuilder;

trait OverwritableParserDescription
{

    private string $overwritableParserDescription;

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setParserDescription(string $description): self
    {
        $this->overwritableParserDescription = $description;

        return $this;
    }

    /**
     * @param Subject $subject
     *
     * @return ResultBuilder
     */
    protected function createResultBuilder(Subject $subject): ResultBuilder
    {
        return $subject->getResultBuilder(
                $this->overwritableParserDescription ?? $this->getDefaultParserDescription($subject)
        );
    }

    /**
     * @param Subject $subject
     *
     * @return string
     */
    abstract protected function getDefaultParserDescription(Subject $subject): string;

}
