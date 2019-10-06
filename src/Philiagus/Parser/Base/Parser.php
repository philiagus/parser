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
    final public function parse($value, Path $path = null)
    {
        if($path === null) {
            $path = new Root('root');
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
     */
    abstract protected function execute($value, Path $path);

}