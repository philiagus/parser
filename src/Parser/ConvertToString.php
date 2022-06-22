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

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class ConvertToString implements Parser
{
    use Chainable, OverwritableChainDescription, TypeExceptionMessage;

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
     * - value: The value currently being parsed
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

    public function parse($value, ?Path $path = null)
    {

        if (!is_string($value)) {
            switch (true) {
                case $value === null && $this->nullValue !== null:
                    return $this->nullValue;
                case is_int($value):
                    return (string) $value;
                case is_float($value):
                    if (is_infinite($value) || is_nan($value)) break;

                    return (string) $value;
                case is_bool($value):
                    if ($this->booleanValues) {
                        return $this->booleanValues[$value];
                    }
                    break;
                case is_array($value):
                    if ($this->implode !== null) {
                        /** @var Parser|null $elementConverter */
                        $elementConverter = $this->implode[2];
                        $convertedElements = [];
                        $path ??= Path::default($value);
                        foreach ($value as $key => $element) {
                            $convertedElement = $elementConverter ?
                                $elementConverter->parse($element, $path->arrayElement((string) $key)) :
                                $element;
                            if (!is_string($convertedElement)) {
                                throw new ParsingException(
                                    $value,
                                    Debug::parseMessage(
                                        $this->implode[1],
                                        [
                                            'value' => $value,
                                            'key' => $key,
                                            'culprit' => $convertedElement,
                                            'culpritRaw' => $element,
                                        ]
                                    ),
                                    $elementConverter ?
                                        $elementConverter->getChainedPath($path) :
                                        $path
                                );
                            }
                            $convertedElements[] = $convertedElement;
                        }

                        return implode($this->implode[0], $convertedElements);
                    }
                    break;
                case is_object($value):
                    if (method_exists($value, '__toString')) return (string) $value;
                    break;
            }

            $this->throwTypeException($value, $path);
        }

        return $value;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Variable of type {value.type} could not be converted to a string';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('convert to string', false);
    }
}
