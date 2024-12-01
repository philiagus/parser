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

namespace Philiagus\Parser\Parser\Assert;

use Philiagus\Parser\Base\Subject;

/**
 * Asserts the value to be a float. This explicitly excludes NAN on principle and INF/-INF by default
 *
 * You can define further assertions on the float value (such as min and max).
 *
 * @package Parser\Assert
 * @target-type float
 * @see static::setAllowInfinite() to allow INF/-INF as valid values
 */
class AssertFloat extends AssertNumber
{

    private bool $allowInfinite = false;

    /**
     * Configures the parser to allow infinite as a valid value
     *
     * @param bool $allowInfinite
     * @return $this
     */
    public function setAllowInfinite(bool $allowInfinite = true): static
    {
        $this->allowInfinite = $allowInfinite;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type float';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'assert float';
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isSupportedType(mixed $value): bool
    {
        return is_float($value) &&
            !is_nan($value) &&
            ($this->allowInfinite || !is_infinite($value));
    }
}
