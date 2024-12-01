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

/**
 * Memory is a utility class used by the subject to store values associated with objects (most times parsers)
 */
class Memory
{
    private \SplObjectStorage $memory;

    public function __construct()
    {
        $this->memory = new \SplObjectStorage();
    }

    /**
     * Returns the memory associated to the provided object or the default
     * if no memory is associated with it.
     *
     * If the associated value is `null` this method will treat it as not set
     * and return the default
     *
     * @param object $of
     * @param mixed|null $default
     * @return mixed
     * @see self::set()
     */
    public function get(object $of, mixed $default = null): mixed
    {
        if ($this->memory->offsetExists($of)) {
            return $this->memory->offsetGet($of);
        }

        return $default;
    }

    /**
     * Associates the provided value with the $of object so that it can be recalled
     * later using get
     * @param object $of
     * @param mixed $value
     * @return void
     * @see self::get()
     */
    public function set(object $of, mixed $value): void
    {
        $this->memory->offsetSet($of, $value);
    }

    /**
     * Returns true if any data has been associated with the provided object.
     * If the associated data is `null` this method will also return `false`
     *
     * @param object $of
     * @return bool
     */
    public function has(object $of): bool
    {
        return $this->memory->offsetExists($of);
    }
}
