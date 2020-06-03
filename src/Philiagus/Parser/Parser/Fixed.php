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
     * @param $value
     *
     * @return static
     * @throws ParserConfigurationException
     */
    public static function value($value): self
    {
        return self::new()->setValue($value);
    }

    /**
     * Sets the value this parser is defined as
     *
     * @param $value
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setValue($value): self
    {
        if ($this->defined) {
            throw new ParserConfigurationException(
                'Value of Fixed cannot be overwritten'
            );
        }

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