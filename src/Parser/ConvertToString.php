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
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Util\Debug;
use Stringable;

class ConvertToString extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var array{string, string}|null */
    private ?array $booleanValues = null;

    private ?string $nullValue = null;

    /** @var null|array{string, string, null|Parser} */
    private ?array $implode = null;

    private function __construct()
    {

    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param string $true
     * @param string $false
     *
     * @return $this
     */
    public function setBooleanValues(string $true, string $false): self
    {
        $this->booleanValues = [$false, $true];

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNullValue(string $value): self
    {
        $this->nullValue = $value;

        return $this;
    }

    /**
     * Specifies a value to implode the array with. Before performing this implode every element inside the array
     * is checked to be a string. If violating elements are found, an exception is thrown
     * The element converter parser can be used to convert elements of the array before type checking them to be string
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - key: The key of the value that was not a string
     * - culprit: The value of the array that wasn't a string (after potential conversion)
     * - culpritRaw: The value of the array before conversion
     *
     * @param string $delimiter
     * @param Parser|null $elementConverter
     * @param string $exceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function setImplodeOfArrays(
        string  $delimiter,
        ?Parser $elementConverter = null,
        string  $exceptionMessage = 'A value at index {key} was not of type string but of type {culprit.type}'
    ): self
    {
        $this->implode = [$delimiter, $exceptionMessage, $elementConverter];

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        if (is_string($value)) {
            return $builder->createResultUnchanged();
        }
        switch (true) {
            case $value === null && $this->nullValue !== null:
                return $builder->createResult($this->nullValue);
            /** @noinspection PhpMissingBreakStatementInspection */
            case is_float($value):
                if (is_infinite($value) || is_nan($value)) break;
            case is_int($value):
                return $builder->createResult((string) $value);
            case is_bool($value):
                if ($this->booleanValues) {
                    return $builder->createResult($this->booleanValues[$value]);
                }
                break;
            case is_array($value):
                if ($this->implode !== null) {
                    /** @var Parser|null $elementConverter */
                    $elementConverter = $this->implode[2];
                    $convertedElements = [];
                    foreach ($value as $key => $element) {
                        $newSubject = new ArrayValue($builder->getSubject(), $key, $element);
                        if ($elementConverter) {
                            $conversionResult = $elementConverter->parse($newSubject);
                            if (!$conversionResult->isSuccess()) {
                                $builder->incorporateResult($conversionResult);
                                continue;
                            }
                            $convertedElement = $conversionResult->getValue();
                        } else {
                            $convertedElement = $element;
                        }

                        if (!is_string($convertedElement)) {
                            $builder->logError(
                                Error::createUsingDebugString(
                                    $newSubject,
                                    $this->implode[1],
                                    [
                                        'key' => $key,
                                        'culprit' => $convertedElement,
                                        'culpritRaw' => $element,
                                    ]
                                )
                            );

                            continue;
                        }

                        $convertedElements[] = $convertedElement;
                    }

                    return $builder->createResult(implode($this->implode[0], $convertedElements));
                }
                break;
            case is_object($value):
                if ($value instanceof Stringable) {
                    return $builder->createResult((string) $value);
                }
                break;
        }

        $this->logTypeError($builder);

        return $builder->createResultUnchanged();
    }

    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Variable of type {subject.type} could not be converted to a string';
    }

    protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'convert to string';
    }
}
