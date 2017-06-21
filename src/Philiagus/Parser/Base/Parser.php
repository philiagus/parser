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

use Philiagus\Parser\Contract;

abstract class Parser implements Contract\Parser
{
    public const PATH_SEPARATOR = "\0";

    /**
     * @var mixed
     */
    private $target;

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
     * @inheritdoc
     */
    final public function parse($value, string $path = '')
    {
        $this->target = $this->convert($value, $path);

        return $this->target;
    }

    /**
     * Real conversion of the provided value into the target value
     * This must be individually implemented by the implementing parser class
     *
     * @param mixed $value
     * @param string $path
     *
     * @return mixed
     */
    abstract protected function convert($value, string $path);

}