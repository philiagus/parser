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
use Philiagus\Parser\Exception\ParserConfigurationException;

class ConvertToArray extends Parser
{
    public const CONVERSION_ARRAY_CAST = 1;
    public const CONVERSION_ARRAY_WITH_KEY = 2;

    /**
     * @var null|1|2
     */
    private $convertNonArrays = null;

    /**
     * @var string|int|null
     */
    private $convertNonArraysOption = null;

    /**
     * @return static
     */
    public static function usingCast(): self
    {
        return (new self())
            ->setConvertToUseCast();
    }

    /**
     * @param $key
     *
     * @return static
     * @throws ParserConfigurationException
     */
    public static function creatingArrayWithKey($key): self
    {
        return (new self())
            ->setConvertToCreateArrayWithKey($key);
    }

    /**
     * @return $this
     */
    public function setConvertToUseCast(): self
    {
        $this->convertNonArrays = self::CONVERSION_ARRAY_CAST;
        $this->convertNonArraysOption = null;

        return $this;
    }

    /**
     * @param $key
     *
     * @return $this
     * @throws ParserConfigurationException
     */
    public function setConvertToCreateArrayWithKey($key): self
    {
        if (!is_string($key) && !is_int($key)) {
            throw new ParserConfigurationException('Array key can only be string or integer');
        }

        $this->convertNonArrays = self::CONVERSION_ARRAY_WITH_KEY;
        $this->convertNonArraysOption = $key;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if($this->convertNonArrays === null) {
            throw new ParserConfigurationException(
                'ConvertToArray parser was not configured with a conversion type'
            );
        }

        if(!is_array($value)) {
            switch ($this->convertNonArrays) {
                case self::CONVERSION_ARRAY_CAST:
                    return (array) $value;
                case self::CONVERSION_ARRAY_WITH_KEY:
                    return [$this->convertNonArraysOption => $value];
            }
        }

        return $value;
    }
}