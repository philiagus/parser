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

namespace Philiagus\Parser\Parser\Parse;

use JsonException;
use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Util\Stringify;

/**
 * Parses as string as JSON and returns the parsed result
 *
 * @see json_decode()
 * @package Parser\Parse
 * @target-type string
 */
class ParseJSONString extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private string $conversionExceptionMessage = 'Provided string is not a valid JSON: {message}';
    private ?bool $objectAsArrays = null;
    private ?int $maxDepth = null;
    private ?bool $bigintAsString = null;

    private function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Sets the error message if the json is invalid or parsing failed
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - message: The json parser error message as provided by json_last_error_msg
     * - code: The error code as provided by json_last_error
     *
     * @param string $errorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see json_last_error()
     * @see json_last_error_msg()
     */
    public function setConversionErrorMessage(string $errorMessage): static
    {
        $this->conversionExceptionMessage = $errorMessage;

        return $this;
    }

    /**
     * Configures the conversion to set $assoc parameter of json_parse
     *
     * @param bool $objectsAsArrays
     *
     * @return $this
     * @see https://www.php.net/manual/de/function.json-decode.php
     */
    public function setObjectsAsArrays(bool $objectsAsArrays = true): static
    {
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
    public function setMaxDepth(int $maxDepth = 512): static
    {
        if ($maxDepth < 1) {
            throw new ParserConfigurationException("The maximum depth for ParseJSONString must be at least 1");
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
     * @see https://www.php.net/manual/de/function.json-decode.php
     */
    public function setBigintAsString(bool $bigintAsString = true): static
    {
        $this->bigintAsString = $bigintAsString;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        $options = JSON_THROW_ON_ERROR;
        if ($this->bigintAsString) {
            $options |= JSON_BIGINT_AS_STRING;
        }

        try {
            $result = json_decode(
                $value,
                $this->objectAsArrays ?? false,
                $this->maxDepth ?? 512,
                $options
            );
        } catch (JsonException $jsonException) {
            $builder->logErrorStringify(
                $this->conversionExceptionMessage,
                ['message' => json_last_error_msg(), 'code' => json_last_error()],
                $jsonException
            );

            return $builder->createResultUnchanged();
        }

        return $builder->createResult($result);
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not a string and thus not a valid JSON';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'parse as JSON';
    }
}
