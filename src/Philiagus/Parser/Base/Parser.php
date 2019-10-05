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

use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Type\AcceptsMixed;

abstract class Parser
{
    public const PATH_SEPARATOR = "\0";

    /**
     * @var mixed
     */
    private $target;

    /**
     * @var AcceptsMixed|null
     */
    private $recovery = null;

    /**
     * @var AcceptsMixed|null
     */
    private $pipeTo = null;

    /**
     * @var AcceptsMixed|null
     */
    private $postprocess = null;

    /**
     * The constructor receives the target to parse into
     *
     * @param mixed $target
     */
    public function __construct(&$target = null)
    {
        $this->target = &$target;
    }

    public function recovery(AcceptsMixed $parser): self
    {
        $this->recovery = $parser;

        return $this;
    }

    public function pipeTo(AcceptsMixed $parser): self
    {
        $this->pipeTo = $parser;

        return $this;
    }

    public function postprocess(AcceptsMixed $parser): self
    {
        $this->postprocess = $parser;

        return $this;
    }

    /**
     * @inheritdoc
     */
    final public function parse($value, Path $path = null)
    {
        if($path === null) {
            $path = new Root('root');
        }

        try {
            $result = $this->execute($value, $path);

            if ($this->pipeTo) {
                $result = $this->pipeTo->parse($result, $path);
            }
        } catch (ParsingException $exception) {
            if (!$this->recovery) {
                throw $exception;
            }
            $result = $this->recovery->parse($value, $path);
        }

        if ($this->postprocess) {
            $result = $this->postprocess->parse($result, $path);
        }

        return $this->target = $result;
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
     */
    abstract protected function execute($value, Path $path);

}