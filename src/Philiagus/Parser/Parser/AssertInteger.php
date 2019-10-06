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
use Philiagus\Parser\Type;

class AssertInteger extends Parser implements Type\AcceptsInteger
{
    /**
     * @var int|null
     */
    private $minimumValue = null;

    /**
     * @var int|null
     */
    private $maximumValue = null;

    /**
     * @var null|int
     */
    private $divisibleByValue = null;

    /**
     * @param int $minimum
     *
     * @return AssertInteger
     * @throws Exception\ParserConfigurationException
     */
    public function withMinimum(int $minimum): self
    {
        if ($this->maximumValue !== null && $this->maximumValue < $minimum) {
            throw new Exception\ParserConfigurationException(
                sprintf('Trying to set minimum of %s to a higher value than the maximum of %s', $minimum, $this->maximumValue)
            );
        }

        $this->minimumValue = $minimum;

        return $this;
    }

    /**
     * @param int $maximum
     *
     * @return AssertInteger
     * @throws Exception\ParserConfigurationException
     */
    public function withMaximum(int $maximum): self
    {
        if ($this->minimumValue !== null && $this->minimumValue > $maximum) {
            throw new Exception\ParserConfigurationException(
                sprintf('Trying to set maximum of %s to a lower value than the minimum of %s', $maximum, $this->minimumValue)
            );
        }

        $this->maximumValue = $maximum;

        return $this;
    }

    /**
     * @param int $divisor
     *
     * @return AssertInteger
     * @throws Exception\ParserConfigurationException
     */
    public function withDivisibleBy(int $divisor): self
    {
        if ($divisor <= 0) {
            throw new Exception\ParserConfigurationException(
                'A divisor of 0 or lower cannot be set'
            );
        }

        $this->divisibleByValue = $divisor;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_int($value)) {
            throw new Exception\ParsingException($value, 'Provided value is not of type integer', $path);
        }

        if ($this->minimumValue !== null && $this->minimumValue > $value) {
            throw new Exception\ParsingException(
                $value, sprintf('Provided value of %s is lower than the defined minimum of %s', $value, $this->minimumValue), $path
            );
        }

        if ($this->maximumValue !== null && $this->maximumValue < $value) {
            throw new Exception\ParsingException(
                $value, sprintf('Provided value of %s is greater than the defined maximum of %s', $value, $this->maximumValue), $path
            );
        }

        if ($this->divisibleByValue !== null && (int) ($value % $this->divisibleByValue) !== 0) {
            throw new Exception\ParsingException(
                $value, sprintf('Provided value of %s is not divisible by %s', $value, $this->divisibleByValue), $path
            );
        }

        return $value;
    }
}