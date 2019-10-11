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
use Philiagus\Parser\Exception\ParsingException;

class AssertFloat extends Parser
{
    /**
     * @var float|null
     */
    private $minimumValue = null;

    /**
     * @var float|null
     */
    private $maximumValue = null;

    /**
     * @param float $minimum
     *
     * @return AssertFloat
     * @throws Exception\ParserConfigurationException
     */
    public function withMinimum(float $minimum): self
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
     * @param float $maximum
     *
     * @return AssertFloat
     * @throws Exception\ParserConfigurationException
     */
    public function withMaximum(float $maximum): self
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
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_float($value) || is_nan($value) || is_infinite($value)) {
            throw new ParsingException($value, 'Provided value is not of type float', $path);
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


        return $value;
    }
}