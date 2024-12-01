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

namespace Philiagus\Parser\Contract;

use Philiagus\Parser\Base\Subject\Memory;

interface MemoryProvider
{

    /**
     * Allows to set a certain value to be remembered in the context of this subject
     * chain. This can be used to preserve a value across multiple parser boundaries.
     *
     * To ensure not two parsers interact with the same memory section accidentally the
     * stores values are associated by the object the memory is supposed to be associated with.
     *
     * In most cases the $of is the parser instance that wants to remember something.
     *
     * @param object $of
     * @param mixed $value
     * @see self::getMemory()
     * @see Memory::set()
     */
    public function setMemory(object $of, mixed $value): void;

    /**
     * Allows access to a certain memory stored within the subject chain.
     *
     * @param object $of
     * @param mixed|null $default The default value to return if the targeted memory is not set yet
     * @return mixed
     * @see self::setMemory()
     * @see Memory::get()
     */
    public function getMemory(object $of, mixed $default = null): mixed;

    /**
     * Returns true if any memory value associated with the object has already been preserved.
     *
     * @param object $of
     * @return bool
     */
    public function hasMemory(object $of): bool;

    /**
     * Returns the full memory of the subject chain.
     *
     * @return Memory
     * @see self::setMemory()
     * @see self::getMemory()
     * @see Memory::has()
     */
    public function getFullMemory(): Memory;

}
