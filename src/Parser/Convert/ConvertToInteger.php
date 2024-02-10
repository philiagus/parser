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

namespace Philiagus\Parser\Parser\Convert;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;

/**
 * Takes any input and attempts a loss free conversion of the provided value into a valid integer value
 * Conversion is attempted for floats and strings
 * Please use other parsers in order to convert input values to strings/floats
 */
class ConvertToInteger extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (is_int($value)) {
            return $builder->createResultUnchanged();
        }

        if (is_float($value)) {
            // invalid float values
            if (
                is_nan($value)
                || $value > PHP_INT_MAX || $value < PHP_INT_MIN
                || $value !== (float)(int)$value
            ) {
                $this->logTypeError($builder);

                return $builder->createResultUnchanged();
            }

            return $builder->createResult((int)$value);
        }

        if (is_string($value)) {
            // string conversion
            if (preg_match('~^(-|)0*([0-9]+)$~', $value, $matches) === 1) {
                if ($matches[2] === '0') {
                    $compareString = '0';
                } else {
                    $compareString = $matches[1] . $matches[2];
                }
                $compareInteger = (int)$compareString;
                if ((string)$compareInteger === $compareString) {
                    return $builder->createResult($compareInteger);
                }
            }
        }
        $this->logTypeError($builder);

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Variable of type {subject.type} could not be converted to an integer';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'convert to integer';
    }
}
