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

use Philiagus\Parser\Contract\Subject;
use Philiagus\Parser\ResultBuilder;

trait OverwritableParserDescription
{

    private string $overwritableParserDescription;

    /**
     * Overwrite the description of this parser used when creating a path string including
     * utility subjects
     *
     * @param string $description
     *
     * @return $this
     */
    public function setParserDescription(string $description): static
    {
        $this->overwritableParserDescription = $description;

        return $this;
    }

    /**
     * Creates a ResultBuilder instance, loading it with the appropriate parser description
     * In order for this trait to do its job correctly, this method has to be used at the start of the parse
     * method of the parser and the ResultBuilder provided has to be used to create
     * the result
     * Bypassing this method or not using that ResultBuilder will result in unexpected behaviour
     *
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
     * Provides a default description for this parser used if the description is not overwritten
     * by the user of the parser using the setParserDescription method
     * @param Subject $subject
     *
     * @return string
     * @see setParserDescription()
     */
    abstract protected function getDefaultParserDescription(Subject $subject): string;

}
