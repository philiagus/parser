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

namespace Philiagus\Parser;

use Philiagus\Parser\Base\Parser;

/**
 * Takes any input and attempts a loss free conversion of the provided value into a valid integer value
 */
class IntegerConverter extends Parser implements
    Type\AcceptsInteger,
    Type\AcceptsString,
    Type\AcceptsFloat
{

    /**
     * @var null|Type\AcceptsInteger
     */
    private $followupParser = null;

    /**
     * @param Type\AcceptsInteger $parser
     *
     * @return IntegerConverter
     */
    public function withFollowupParser(Type\AcceptsInteger $parser): self
    {
        $this->followupParser = $parser;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function convert($value, string $path)
    {
        if (!is_int($value)) {
            if (is_float($value) && !is_nan($value) && !is_infinite($value)) {
                // float conversion
                if ((string) $value == (string) (int) $value) {
                    $value = (int) $value;
                }

                if (!is_int($value)) {
                    throw new Exception\ParsingException(
                        sprintf('The provided float value %s could not be converted to an integer', $value),
                        $path
                    );
                }
            } elseif (is_string($value)) {
                // string conversion
                if (preg_match('~^(-|)0*([0-9]+)$~', $value, $matches) === 1) {
                    if ($matches[2] === '0') {
                        $compareString = '0';
                    } else {
                        $compareString = $matches[1] . $matches[2];
                    }
                    $compareInteger = (int) $compareString;
                    if ((string) $compareInteger === $compareString) {
                        $value = $compareInteger;
                    }
                }

                if (!is_int($value)) {
                    throw new Exception\ParsingException(
                        sprintf('The provided string value could not be converted to an integer', $value),
                        $path
                    );
                }
            } else {
                throw new Exception\ParsingException(
                    sprintf('Variable of type %s could not be converted to an integer', gettype($value)),
                    $path
                );
            }
        }

        if ($this->followupParser) {
            $value = $this->followupParser->parse($value, $path);
        }

        return $value;
    }
}