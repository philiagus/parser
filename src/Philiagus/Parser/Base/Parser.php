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

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Root;

abstract class Parser
{
    /**
     * @var mixed
     */
    private $target;

    /**
     * @var null|self
     */
    private $then = null;

    /**
     * The constructor receives the target to parse into
     *
     * @param mixed $target
     */
    public function __construct(&$target = null)
    {
        $this->target = &$target;
    }

    /**
     * Static version of the constructor for more readable creation when chaining calls
     *
     * @param null $target
     *
     * @return static
     */
    public static function new(&$target = null): self
    {
        return new static($target);
    }

    /**
     * @param mixed $value
     * @param Path|null $path
     *
     * @return mixed
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    final public function parse($value, Path $path = null)
    {
        if ($path === null) {
            $path = new Root('root');
        }

        if ($this->then) {
            return $this->target = $this->then->parse(
                $this->execute($value, $path),
                $path
            );
        }

        return $this->target = $this->execute($value, $path);
    }

    /**
     * Real conversion of the provided value into the target value
     * This must be individually implemented by the implementing parser class
     *
     * @param mixed $value
     * @param Path $path
     *
     * @return mixed
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    abstract protected function execute($value, Path $path);

    /**
     * Appends a parser to execute once this parser has done its job
     * The result of this parser is identical to the result of the chained parser
     *
     * @param Parser $parser
     *
     * @return $this
     */
    public function then(Parser $parser): self
    {
        $this->then = $parser;

        return $this;
    }

}