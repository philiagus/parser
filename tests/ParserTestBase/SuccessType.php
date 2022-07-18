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

namespace Philiagus\Parser\Test\ParserTestBase;

enum SuccessType {

    case SUCCESS;
    case ERROR_THROW;
    case ERROR_NON_THROW;

    public function isSuccess(): bool
    {
        return $this === self::SUCCESS;
    }

}
