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

namespace Philiagus\Parser\Parser\Convert;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Util\Debug;
use Stringable;

class ConvertToString extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private ?string $trueValue = null;
    private ?string $falseValue = null;

    private ?string $nullValue = null;

    /** @var null|array{string, string, null|Parser} */
    private ?array $implode = null;
    private null|int $numberFormat_Decimals = null;
    private null|string $numberFormat_ThousandsSeparator = null;
    private null|string $numberFormat_DecimalSeparator = null;

    private function __construct()
    {

    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Defines the string representations of true and false
     *
     * @param string $true
     * @param string $false
     *
     * @return $this
     */
    public function setBooleanValues(string $true, string $false): static
    {
        $this->trueValue = $true;
        $this->falseValue = $false;

        return $this;
    }

    /**
     * Defines the string representation of null values
     *
     * @param string $value
     *
     * @return $this
     */
    public function setNullValue(string $value): static
    {
        $this->nullValue = $value;

        return $this;
    }

    /**
     * Specifies a value to implode the array with. Before performing this implodes every element inside the array
     * is checked to be a string. If violating elements are found, an error is generated
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
     * @param string $errorMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function setImplodeOfArrays(
        string  $delimiter,
        ?Parser $elementConverter = null,
        string $errorMessage = 'A value at index {key} was not of type string but of type {culprit.type}'
    ): static
    {
        $this->implode = [$delimiter, $errorMessage, $elementConverter];

        return $this;
    }

    /**
     * Defines number conversion to use number_format with the provided arguments
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandsSeparator
     * @return $this
     * @see number_format()
     */
    public function setNumberFormat(int $decimals, string $decimalSeparator = '.', string $thousandsSeparator = ','): static
    {
        $this->numberFormat_Decimals = $decimals;
        $this->numberFormat_DecimalSeparator = $decimalSeparator;
        $this->numberFormat_ThousandsSeparator = $thousandsSeparator;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
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
                if ($this->numberFormat_Decimals !== null) {
                    return $builder->createResult(
                        number_format(
                            $value,
                            $this->numberFormat_Decimals,
                            $this->numberFormat_DecimalSeparator,
                            $this->numberFormat_ThousandsSeparator
                        )
                    );
                }
                return $builder->createResult((string)$value);
            case is_bool($value):
                if ($value && $this->trueValue !== null) {
                    return $builder->createResult($this->trueValue);
                } elseif (!$value && $this->falseValue !== null) {
                    return $builder->createResult($this->falseValue);
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
                                $builder->unwrapResult($conversionResult);
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
                    return $builder->createResult((string)$value);
                }
                break;
        }

        $this->logTypeError($builder);

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Variable of type {subject.type} could not be converted to a string';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'convert to string';
    }
}
