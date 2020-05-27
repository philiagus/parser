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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception;

class AssertInteger extends Parser
{
    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of type integer';

    /**
     * @var callable[]
     */
    private $assertionList = [];

    /**
     * Sets the exception message thrown when the type does not match
     *
     * @param string $message
     *
     * @return $this
     */
    public function overwriteTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Asserts that the value is >= the provided minimum
     * Replacers in the exception message:
     * {value} = parsed value
     * {min} = currently set minimum
     *
     * @param int $minimum
     * @param string $exceptionMessage
     *
     * @return AssertInteger
     */
    public function withMinimum(int $minimum, string $exceptionMessage = 'Provided value of {value} is lower than the defined minimum of {min}'): self
    {
        $this->assertionList[] = function (int $value, Path $path) use ($minimum, $exceptionMessage) {
            if ($minimum > $value) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exceptionMessage, ['{value}' => $value, '{min}' => $minimum]),
                    $path
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is <= the provided maximum
     * Replacers in the exception message:
     * {value} = parsed value
     * {max} = currently set maximum
     *
     * @param int $maximum
     * @param string $exceptionMessage
     *
     * @return AssertInteger
     */
    public function withMaximum(int $maximum, string $exceptionMessage = 'Provided value of {value} is greater than the defined maximum of {max}}'): self
    {
        $this->assertionList[] = function (int $value, Path $path) use ($maximum, $exceptionMessage) {
            if ($maximum < $value) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exceptionMessage, ['{value}' => $value, '{max}' => $maximum]),
                    $path
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the value is a multiple of the base
     * Replacers in the exception message:
     * {value} = parsed value
     * {base} = currently set base
     *
     * @param int $base
     * @param string $exceptionMessage
     *
     * @return AssertInteger
     */
    public function isMultipleOf(
        int $base,
        string $exceptionMessage = 'Provided value of {value} is not a multiple of {base}'
    ): self
    {
        $this->assertionList[] = function (int $value, Path $path) use ($base, $exceptionMessage) {
            if($value === 0 && $base === 0) return;
            if (($value % $base) !== 0) {
                throw new Exception\ParsingException(
                    $value,
                    strtr($exceptionMessage, ['{value}' => $value, '{base}' => $base]),
                    $path
                );
            }
        };

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_int($value)) {
            throw new Exception\ParsingException($value, $this->typeExceptionMessage, $path);
        }

        foreach($this->assertionList as $assertion) {
            $assertion($value, $path);
        }

        return $value;
    }
}