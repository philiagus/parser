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

namespace Philiagus\Parser\Subject;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Util\Stringify;

/**
 * The root subject normally used to start a parsing process
 *
 * @package Subject
 */
class Root extends Subject
{

    public function __construct(mixed $value, ?string $description = null, bool $throwOnError = true)
    {
        parent::__construct(
            null,
            $description ?? Stringify::getType($value),
            $value,
            false,
            $throwOnError
        );
    }

    /** @inheritDoc */
    #[\Override] protected function getPathStringPart(bool $isLastInChain): string
    {
        return $this->description;
    }
}
