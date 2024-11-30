<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Base\Subject;

use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Subject;

/**
 * Memory is a utility class used by the subject to store values associated with objects (most times parsers)
 *
 * @see Subject::setMemory()
 * @see Subject::getMemory()
 * @see Subject::hasMemory()
 */
class Memory implements Contract\Subject\Memory
{
    private \SplObjectStorage $memory;

    public function __construct()
    {
        $this->memory = new \SplObjectStorage();
    }

    /**
     * @param object $of
     * @param mixed|null $default
     * @return mixed
     */
    public function get(object $of, mixed $default = null): mixed
    {
        if ($this->memory->offsetExists($of)) {
            return $this->memory->offsetGet($of);
        }

        return $default;
    }

    /**
     * @param object $of
     * @param mixed $value
     * @return void
     */
    public function set(object $of, mixed $value): void
    {
        $this->memory->offsetSet($of, $value);
    }

    /**
     * @param object $of
     * @return bool
     */
    public function has(object $of): bool
    {
        return $this->memory->offsetExists($of);
    }
}
