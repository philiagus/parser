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
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

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
     * @var bool|null
     */
    private $objectAsArrays = null;

    /**
     * @var int|null
     */
    private $maxDepth = null;

    /**
     * @var bool|null
     */
    private $bigintAsString = null;

    /**
     * Sets the exception message if the json is invalid or parsing failed
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - msg: The json parser error message
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function overwriteConversionExceptionMessage(string $message): self
    {
        $this->conversionExceptionMessage = $message;

        return $this;
    }

    /**
     * Sets the exception message thrown when the provided value is not a string
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function overwriteTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Configures the conversion to set $assoc parameter of json_parse
     *
     * @param bool $objectsAsArrays
     *
     * @return $this
     * @throws ParserConfigurationException
     * @see https://www.php.net/manual/de/function.json-decode.php
     */
    public function setObjectsAsArrays(bool $objectsAsArrays = true): self
    {
        if ($this->objectAsArrays !== null) {
            throw new ParserConfigurationException(
                'Cannot overwrite objectAsArray configuration once set'
            );
        }
        $this->objectAsArrays = $objectsAsArrays;

        return $this;
    }

    /**
     * Sets the max depth of the json_parse
     *
     * @param int $maxDepth
     *
     * @return $this
     * @throws ParserConfigurationException
     * @see https://www.php.net/manual/de/function.json-decode.php
     */
    public function setMaxDepth(int $maxDepth = 512): self
    {
        if ($this->maxDepth !== null) {
            throw new ParserConfigurationException(
                'Cannot overwrite maxDepth configuration once set'
            );
        }
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Configures the decoding to use bigints as strings
     *
     * @param bool $bigintAsString
     *
     * @return $this
     * @throws ParserConfigurationException
     * @see https://www.php.net/manual/de/function.json-decode.php
     */
    public function setBigintAsString(bool $bigintAsString = true): self
    {
        if ($this->bigintAsString !== null) {
            throw new ParserConfigurationException(
                'Cannot overwrite bigintAsString configuration once set'
            );
        }
        $this->bigintAsString = $bigintAsString;

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
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        $options = 0;
        if ($this->bigintAsString) {
            $options |= JSON_BIGINT_AS_STRING;
        }

        $result = @json_decode(
            $value,
            $this->objectAsArrays ?? false,
            $this->maxDepth ?? 512,
            $options
        );
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->conversionExceptionMessage, ['msg' => json_last_error_msg(), 'value' => $value]),
                $path
            );
        }

        return $result;
    }
}