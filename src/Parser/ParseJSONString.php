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

namespace Philiagus\Parser\Parser;

use JsonException;
use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

/**
 * Parses as string as JSON and returns the parsed result
 *
 * @see json_decode()
 */
class ParseJSONString extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var string */
    private string $conversionExceptionMessage = 'Provided string is not a valid JSON: {msg}';
    /** @var bool|null */
    private ?bool $objectAsArrays = null;
    /** @var int|null */
    private ?int $maxDepth = null;
    /** @var bool|null */
    private ?bool $bigintAsString = null;

    private function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Sets the exception message if the json is invalid or parsing failed
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - msg: The json parser error message
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function setConversionExceptionMessage(string $message): static
    {
        $this->conversionExceptionMessage = $message;

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
            $builder->logErrorUsingDebug(
                $this->conversionExceptionMessage,
                ['msg' => json_last_error_msg()],
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
