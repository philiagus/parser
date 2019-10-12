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
use Philiagus\Parser\Exception\ParsingException;
use function json_decode;

class ConvertFromJson extends Parser
{

    /**
     * @var string
     */
    private $conversionExceptionMessage = 'Provided string is not a valid JSON: {msg}';

    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not a string and thus not a valid JSON';

    /**
     * @var bool
     */
    private $objectAsArrays = false;

    /**
     * @var int
     */
    private $maxDepth = 512;

    /**
     * @var bool
     */
    private $bigintAsString = false;

    /**
     * Sets the exception message if the json is invalid or parsing failed
     * Available replacers:
     * {msg} = the json parser error message
     *
     * @param string $message
     *
     * @return $this
     */
    public function withConversionExceptionMessage(string $message): self
    {
        $this->conversionExceptionMessage = $message;

        return $this;
    }

    /**
     * Sets the exception message thrown when the provided value is not a string
     * Available replacers:
     * {type} = gettype of the provided value
     * @param string $message
     *
     * @return $this
     */
    public function withTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    public function withObjectsAsArrays(): self
    {
        $this->objectAsArrays = true;

        return $this;
    }

    public function withMaxDepth(int $maxDepth = 512): self
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    public function withBigintAsString(): self
    {
        $this->bigintAsString = true;

        return $this;
    }

    /**
     * Real conversion of the provided value into the target value
     * This must be individually implemented by the implementing parser class
     *
     * @param mixed $value
     * @param Path $path
     *
     * @return mixed
     * @throws ParsingException
     */
    protected function execute($value, Path $path)
    {
        if (!is_string($value)) {
            throw new ParsingException(
                $value,
                strtr($this->typeExceptionMessage, ['{type}' => gettype($value)]),
                $path
            );
        }

        $options = 0;
        if ($this->bigintAsString) {
            $options |= JSON_BIGINT_AS_STRING;
        }

        $result = @json_decode($value, $this->objectAsArrays, $this->maxDepth, $options);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParsingException(
                $value,
                strtr($this->conversionExceptionMessage, ['{msg}' => json_last_error_msg()]),
                $path
            );
        }

        return $result;
    }
}