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

trait OverridableChainDescription
{

    private array $overridableChainDescription;

    /**
     * @param string $description
     * @param bool $isPathInValue
     *
     * @return $this
     */
    public function setChainDescription(string $description, bool $isPathInValue = true): self
    {
        $this->overridableChainDescription = [$description, $isPathInValue];

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
        return isset($this->overridableChainDescription) ?
            $path->chain(
                $this->overridableChainDescription[0],
                $this->overridableChainDescription[1]
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
