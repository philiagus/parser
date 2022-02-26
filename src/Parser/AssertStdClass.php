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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableChainDescription;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class AssertStdClass implements Parser
{
    use Chainable, OverwritableChainDescription, TypeExceptionMessage;

    /** @var callable[] */
    protected array $assertionList = [];

    final private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function new()
    {
        return new static();
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * If the key does not exist an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - property: The missing property as defined here
     *
     * @param string $property
     * @param ParserContract $parser
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function givePropertyValue(
        string $property, ParserContract $parser,
        string $missingKeyExceptionMessage = 'The object does not contain the requested property {property}'
    ): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path) use ($property, $parser, $missingKeyExceptionMessage) {
            if (!property_exists($value, $property)) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($missingKeyExceptionMessage, ['property' => $property, 'value' => $value]),
                    $path
                );
            }

            $parser->parse($value->$property, $path->propertyValue($property));

            return $value;
        };

        return $this;
    }

    /**
     *
     * @inheritdoc
     */
    public function parse($value, ?Path $path = null)
    {
        if (!is_object($value) || get_class($value) !== \stdClass::class) {
            $this->throwTypeException($value, $path);
        }

        $path ??= Path::default($value);
        foreach ($this->assertionList as $assertion) {
            $value = $assertion($value, $path);
        }

        return $value;
    }

    /**
     * If a property of the given name exists, the value of that property is provided to the parser
     *
     * @param string $property
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveOptionalPropertyValue(string $property, ParserContract $parser): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path) use ($property, $parser) {
            if (property_exists($value, $property)) {
                $parser->parse($value->$property, $path->propertyValue($property));
            }

            return $value;
        };

        return $this;
    }

    public function giveDefaultedPropertyValue(string $property, $default, ParserContract $parser): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path) use ($property, $default, $parser) {
            $propertyValue = $default;
            if (property_exists($value, $property)) {
                $propertyValue = $value->$property;
            }
            $parser->parse($propertyValue, $path->propertyValue($property));
        };

        return $this;
    }

    /**
     * @param ParserContract $arrayParser
     *
     * @return $this
     */
    public function givePropertyNames(ParserContract $arrayParser): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path) use ($arrayParser) {
            $properties = [];
            foreach ($value as $property => $_) {
                $properties[] = $property;
            }
            $arrayParser->parse($properties, $path->meta('property names'));
        };

        return $this;
    }

    /**
     * Loops through the names of the properties of the object and hands each of them individually to the parser
     *
     * @param ParserContract $stringParser
     *
     * @return $this
     */
    public function giveEachPropertyName(ParserContract $stringParser): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path) use ($stringParser) {
            foreach ($value as $property => $_) {
                $stringParser->parse($property, $path->propertyName((string) $property));
            }

            return $value;
        };

        return $this;
    }

    /**
     * Loops through the properties of the object and hands each value individually to the parser
     *
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveEachPropertyValue(ParserContract $parser): self
    {
        $this->assertionList[] = function (\stdClass $stdClass, Path $path) use ($parser) {
            foreach ($stdClass as $property => $value) {
                $parser->parse($value, $path->propertyName((string) $property));
            }

            return $stdClass;
        };

        return $this;
    }

    /**
     * Sends the count of properties to the provided parser
     *
     * @param ParserContract $integerParser
     *
     * @return $this
     */
    public function givePropertyCount(ParserContract $integerParser): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path) use ($integerParser) {
            $count = 0;
            foreach ($value as $_) {
                $count++;
            }
            $integerParser->parse($count, $path->meta('property count'));

            return $value;
        };

        return $this;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not an instance of \stdClass';
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('assert stdClass', false);
    }
}
