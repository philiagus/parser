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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

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

    public static function new(): self
    {
        return new self();
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
    public function setConversionExceptionMessage(string $message): self
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
    public function setObjectsAsArrays(bool $objectsAsArrays = true): self
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
    public function setMaxDepth(int $maxDepth = 512): self
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
    public function setBigintAsString(bool $bigintAsString = true): self
    {
        $this->bigintAsString = $bigintAsString;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
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
            $builder->logErrorUsingDebug(
                $this->conversionExceptionMessage,
                ['msg' => json_last_error_msg()]
            );

            return $builder->createResultUnchanged();
        }

        return $builder->createResult($result);
    }

    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not a string and thus not a valid JSON';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'parse as JSON';
    }
}
