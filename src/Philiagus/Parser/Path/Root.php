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

namespace Philiagus\Parser\Path;

use Philiagus\Parser\Base\Path;

class Root extends Path
{

    public function __construct($name = 'root')
    {
        parent::__construct($name);
    }

    protected function getDelimiter(): string
    {
        return '';
    }
}
