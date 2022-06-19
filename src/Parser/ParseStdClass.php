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

use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Util\Debug;

class ParseStdClass extends AssertStdClass
{

    public function defaultProperty(string $property, $defaultValue): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path, bool &$alreadyCloned) use ($property, $defaultValue) {
            if (!property_exists($value, $property)) {
                if(!$alreadyCloned) {
                    $value = clone $value;
                    $alreadyCloned = true;
                }
                $value->$property = $defaultValue;
            }

            return $value;
        };

        return $this;
    }

    public function defaultWith(\stdClass $object): self
    {
        $this->assertionList[] = function (\stdClass $stdClass, Path $path, bool &$alreadyCloned) use ($object) {
            if(!$alreadyCloned) {
                $stdClass = clone $stdClass;
                $alreadyCloned = true;
            }
            foreach ($object as $property => $value) {
                if (!property_exists($stdClass, $property)) {
                    $stdClass->$property = $value;
                }
            }

            return $stdClass;
        };

        return $this;
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
    public function modifyPropertyValue(
        string $property, ParserContract $parser,
        string $missingKeyExceptionMessage = 'The object does not contain the requested property {property}'
    ): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path, bool &$alreadyCloned) use ($property, $parser, $missingKeyExceptionMessage) {
            if (!property_exists($value, $property)) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage($missingKeyExceptionMessage, ['property' => $property, 'value' => $value]),
                    $path
                );
            }

            if(!$alreadyCloned) {
                $value = clone $value;
                $alreadyCloned = true;
            }
            $value->$property = $parser->parse($value->$property, $path->propertyValue($property));

            return $value;
        };

        return $this;
    }

    /**
     * If a property of the given name exists, the value of that property is provided to the parser
     *
     * @param string $property
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function modifyOptionalPropertyValue(string $property, ParserContract $parser): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path, bool &$alreadyCloned) use ($property, $parser) {
            if (property_exists($value, $property)) {
                if(!$alreadyCloned) {
                    $value = clone $value;
                    $alreadyCloned = true;
                }
                $value->$property = $parser->parse($value->$property, $path->propertyValue($property));
            }

            return $value;
        };

        return $this;
    }

    /**
     * Loops through the names of the properties of the object and hands each of them individually to the parser
     *
     * @param ParserContract $stringParser
     * @param string $newPropertyNameIsNotUsableMessage
     *
     * @return $this
     */
    public function modifyEachPropertyName(
        ParserContract $stringParser,
        string         $newPropertyNameIsNotUsableMessage = 'Modifying the property name "{property.raw}" resulted in an invalid type {value.type}, expected string'
    ): self
    {
        $this->assertionList[] = function (\stdClass $value, Path $path, bool &$alreadyCloned) use ($newPropertyNameIsNotUsableMessage, $stringParser) {
            $result = new \stdClass();
            $alreadyCloned = true;
            foreach ($value as $property => $propValue) {
                $newName = $stringParser->parse($property, $path->propertyName((string) $property));
                if (!is_string($newName)) {
                    throw new Exception\ParserConfigurationException(
                        Debug::parseMessage(
                            $newPropertyNameIsNotUsableMessage,
                            [
                                'property' => $property,
                                'value' => $newName,
                            ]
                        )
                    );
                }
                $result->$newName = $propValue;
            }

            return $result;
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
    public function modifyEachPropertyValue(ParserContract $parser): self
    {
        $this->assertionList[] = function (\stdClass $stdClass, Path $path, bool &$alreadyCloned) use ($parser) {
            $result = new \stdClass();
            $alreadyCloned = true;
            foreach ($stdClass as $property => $value) {
                $result->$property = $parser->parse($value, $path->propertyName((string) $property));
            }

            return $result;
        };

        return $this;
    }

    protected function getDefaultChainPath(Path $path): Path
    {
        return $path->chain('parse stdClass', false);
    }
}
