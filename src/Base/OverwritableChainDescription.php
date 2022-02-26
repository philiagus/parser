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

trait OverwritableChainDescription
{

    private array $overwritableChainDescription;

    /**
     * @param string $description
     * @param bool $isPathInValue
     *
     * @return $this
     */
    public function setChainDescription(string $description, bool $isPathInValue = true): self
    {
        $this->overwritableChainDescription = [$description, $isPathInValue];

        return $this;
    }

    /**
     * Default implementation of Parser::getChainedPath for this trait
     *
     * @param Path $path
     *
     * @return Path
     * @see Parser::getChainedPath()
     */
    public function getChainedPath(Path $path): Path
    {
        return isset($this->overwritableChainDescription) ?
            $path->chain(
                $this->overwritableChainDescription[0],
                $this->overwritableChainDescription[1]
            ) :
            $this->getDefaultChainPath($path);
    }

    /**
     * @param Path $path
     *
     * @return Path
     */
    abstract protected function getDefaultChainPath(Path $path): Path;

}
