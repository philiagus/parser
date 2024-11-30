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

namespace Philiagus\Parser\Contract\Subject;

interface Memory
{
    public function get(object $of, mixed $default = null): mixed;

    public function set(object $of, mixed $value): void;

    public function has(object $of): bool;
}
