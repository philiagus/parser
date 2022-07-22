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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;


/**
 * Takes any input and attempts a loss free conversion of the provided value into a valid integer value
 */
class ConvertToInteger implements Parser
{
    use Chainable, OverwritableParserDescription, TypeExceptionMessage;

    private function __construct()
    {

    }

    public static function new(): self
    {
        return new self();
    }

    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        $value = $builder->getCurrentValue();
        if (is_int($value)) {
            return $builder->createResultUnchanged();
        }

        if (is_float($value)) {
            // invalid float values
            if (is_nan($value) || is_infinite($value) || $value !== (float) (int) $value) {
                $this->logTypeError($builder);

                return $builder->createResultUnchanged();
            }

            return $builder->createResult((int) $value);
        }

        if (is_string($value)) {
            // string conversion
            if (preg_match('~^(-|)0*([0-9]+)$~', $value, $matches) === 1) {
                if ($matches[2] === '0') {
                    $compareString = '0';
                } else {
                    $compareString = $matches[1] . $matches[2];
                }
                $compareInteger = (int) $compareString;
                if ((string) $compareInteger === $compareString) {
                    return $builder->createResult($compareInteger);
                }
            }
        }
        $this->logTypeError($builder);

        return $builder->createResultUnchanged();
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Variable of type {subject.type} could not be converted to an integer';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'convert to integer';
    }
}
