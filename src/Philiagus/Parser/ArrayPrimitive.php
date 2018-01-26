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

class ArrayPrimitive extends Parser implements
    Type\AcceptsArray
{
    /**
     * @var int|null
     */
    private $minimumCount = null;

    /**
     * @var int|null
     */
    private $maximumCount = null;

    /**
     * @var null|Parser
     */
    private $value = null;

    /**
     * @param int $minimum
     *
     * @return ArrayPrimitive
     * @throws Exception\ParserConfigurationException
     */
    public function withMinimumCount(int $minimum): self
    {
        if($minimum < 0) {
            throw new Exception\ParserConfigurationException(
                sprintf('Minimum count cannot be configured to a lower value than 0, %s provided', $minimum)
            );
        }

        if ($this->maximumCount !== null && $this->maximumCount < $minimum) {
            throw new Exception\ParserConfigurationException(
                sprintf('Trying to set minimum count of %s to a higher value than the maximum of %s', $minimum, $this->maximumCount)
            );
        }

        $this->minimumCount = $minimum;

        return $this;
    }

    /**
     * @param int $maximum
     *
     * @return ArrayPrimitive
     * @throws Exception\ParserConfigurationException
     */
    public function withMaximumCount(int $maximum): self
    {
        if($maximum < 0) {
            throw new Exception\ParserConfigurationException(
                sprintf('Maximum count cannot be configured to a lower value than 0, %s provided', $maximum)
            );
        }

        if ($this->minimumCount !== null && $this->minimumCount > $maximum) {
            throw new Exception\ParserConfigurationException(
                sprintf('Trying to set maximum count of %s to a lower value than the minimum of %s', $maximum, $this->minimumCount)
            );
        }

        $this->maximumCount = $maximum;

        return $this;
    }

    /**
     * @param Parser $parser
     *
     * @return ArrayPrimitive
     */
    public function withValue(Parser $parser): self
    {
        $this->value = $parser;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function convert($value, string $path)
    {
        if(!is_array($value)) {
            throw new Exception\ParsingException('Provided value is not an array', $path);
        }

        if($this->maximumCount !== null || $this->minimumCount !== null) {
            $count = count($value);

            if($this->maximumCount !== null && $count > $this->maximumCount) {
                throw new Exception\ParsingException(
                    sprintf(
                        'Provided array contains %s elements, a maximum of %s is allowed',
                        $count,
                        $this->maximumCount
                    ),
                $path
                );
            }

            if($this->minimumCount !== null && $count < $this->minimumCount) {
                throw new Exception\ParsingException(
                    sprintf(
                        'Provided array contains %s elements, a minimum of %s is required',
                        $count,
                        $this->minimumCount
                    ),
                    $path
                );
            }
        }

        if($this->value) {
            foreach($value as $index => &$element) {
                $element = $this->value->parse($value, $path . self::PATH_SEPARATOR . $index);
            }
        }

        return $value;
    }
}