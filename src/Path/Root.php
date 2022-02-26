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

    /**
     * @param string $description
     */
    public function __construct(string $description = 'root')
    {
        parent::__construct($description);
    }

    /**
     * @inheritDoc
     */
    protected function getStringPart(): string
    {
        return $this->getDescription();
    }
}
