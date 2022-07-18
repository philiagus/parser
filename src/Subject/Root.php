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

namespace Philiagus\Parser\Subject;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Util\Debug;

class Root extends Subject
{

    public function __construct(
        mixed $value,
        ?string $description = null,
        bool $throwOnError = true
    ) {
        parent::__construct(
            $value,
            $description ?? Debug::getType($value),
            throwOnError: $throwOnError
        );
    }

    /**
     * @inheritDoc
     */
    protected function getPathStringPart(): string
    {
        return $this->getPathDescription();
    }
}
