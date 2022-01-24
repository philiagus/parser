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

trait OverridableChainDescription {

    private string $overridableChainDescription;

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setChainDescription(string $description): self
    {
        $this->overridableChainDescription = $description;

        return $this;
    }

    /**
     * @param Path $path
     *
     * @return Path
     */
    abstract protected function getDefaultChainPath(Path $path): Path;

    /**
     * Default implementation of Parser::getChainedPath for this trait
     * @see Parser::getChainedPath()
     * @param Path $path
     *
     * @return Path
     */
    public function getChainedPath(Path $path): Path
    {
        return isset($this->overridableChainDescription) ?
            $path->chain($this->overridableChainDescription) :
            $this->getDefaultChainPath($path);
    }

}
