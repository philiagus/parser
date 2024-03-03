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

namespace Philiagus\Parser\Test\Mock;

enum BackedEnumMock: string {

    case VALUE1 = 'VALUE3';
    case VALUE2 = 'VALUE2';
    case VALUE3 = 'VALUE1';
    case VALUE4 = 'val';

}
