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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception\ParserConfigurationException;

/**
 * Class Fixed
 *
 * The Fixed parser ignores its received value and replaces it with a predefined value
 *
 * @package Philiagus\Parser\Parser
 */
class Fixed extends Parser
{
    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @var bool
     */
    private $defined = false;

    /**
     * Sets the value this parser is defined as
     *
     * @param $value
     *
     * @return $this
     */
    public function withValue($value): self
    {
        $this->value = $value;
        $this->defined = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        if (!$this->defined) {
            throw new ParserConfigurationException('Fixed value was not defined');
        }

        return $this->value;
    }
}