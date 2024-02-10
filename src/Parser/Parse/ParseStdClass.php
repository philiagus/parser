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

use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Parser\Assert\AssertStdClass;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\PropertyName;
use Philiagus\Parser\Subject\PropertyValue;
use Philiagus\Parser\Util\Debug;

class ParseStdClass extends AssertStdClass
{

    /**
     * If the specified property does not exist in the stdClass it is added with the provided value
     *
     * @param string $property
     * @param mixed $defaultValue
     *
     * @return $this
     */
    public function defaultProperty(string $property, mixed $defaultValue): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedProperties) use ($property, $defaultValue): void {
            $value = $builder->getValue();
            $targetedProperties[] = $property;
            if (property_exists($value, $property))
                return;

            $value = clone $value;
            $value->$property = $defaultValue;

            $builder->setValue(
                "defaulted property '$property'", $value
            );
        };

        return $this;
    }

    /**
     * Unions the parsed stdClass with the provided stdClass, adding missing properties as needed
     * No value is overwritten, only missing properties are added
     *
     * @param \stdClass $object
     *
     * @return $this
     */
    public function defaultWith(\stdClass $object): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedProperties) use ($object): void {
            $value = $builder->getValue();
            $cloned = false;
            foreach ($object as $property => $newValue) {
                $targetedProperties[] = $property;
                if (!property_exists($value, $property)) {
                    if (!$cloned) {
                        $cloned = true;
                        $value = clone $value;
                    }
                    $value->$property = $newValue;
                }
            }

            if (!$cloned) return;

            $builder->setValue(
                'defaulted with object', $value
            );
        };

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * In case the key does not exist an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
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
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedProperties)
        use ($property, $parser, $missingKeyExceptionMessage): void {
            $value = $builder->getValue();
            if (!property_exists($value, $property)) {
                $builder->logErrorUsingDebug(
                    $missingKeyExceptionMessage,
                    ['property' => $property]
                );

                return;
            }
            $targetedProperties[] = $property;

            $result = $parser->parse(
                new PropertyValue($builder->getSubject(), $property, $value->$property)
            );
            if ($result->isSuccess()) {
                $value = clone $value;
                $value->$property = $result->getValue();

                $builder->setValue("modify property '$property'", $value);

                return;
            }

            $builder->unwrapResult($result);
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
    public function modifyOptionalPropertyValue(string $property, ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedProperties) use ($property, $parser): void {
            $value = $builder->getValue();
            if (!property_exists($value, $property))
                return;

            $targetedProperties[] = $property;

            $result = $parser->parse(
                new PropertyValue($builder->getSubject(), $property, $value->$property)
            );

            if ($result->isSuccess()) {
                $value = clone $value;
                $value->$property = $result->getValue();
                $builder->setValue("modify property '$property' value", $value);

                return;
            }

            $builder->unwrapResult($result);
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
        string         $newPropertyNameIsNotUsableMessage = 'Modifying the property name "{old.raw}" resulted in an invalid type {new.type}, expected string'
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedProperties) use ($newPropertyNameIsNotUsableMessage, $stringParser): void {
            $result = new \stdClass();
            $value = $builder->getValue();
            $targetedProperties = [];
            foreach ($value as $oldName => $propValue) {
                $newNameResult = $stringParser->parse(
                    new PropertyName($builder->getSubject(), $oldName)
                );
                if ($newNameResult->isSuccess()) {
                    $newName = $newNameResult->getValue();
                    if (!is_string($newName)) {
                        throw new Exception\RuntimeParserConfigurationException(
                            Debug::parseMessage(
                                $newPropertyNameIsNotUsableMessage,
                                ['old' => $oldName, 'new' => $newName]
                            )
                        );
                    }
                    $targetedProperties[] = $newName;
                    $result->$newName = $propValue;

                    continue;
                }

                $builder->unwrapResult($newNameResult);
            }

            $builder->setValue('modify each property name', $result);
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
    public function modifyEachPropertyValue(ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            $result = new \stdClass();
            foreach ($builder->getValue() as $property => $value) {
                $newValueResult = $parser->parse(
                    new PropertyValue($builder->getSubject(), $property, $value)
                );
                if ($newValueResult->isSuccess()) {
                    $result->$property = $newValueResult->getValue();

                    continue;
                }
                $builder->unwrapResult($newValueResult);
                $result->$property = $value;
            }

            $builder->setValue('modify each property value', $result);
        };

        return $this;
    }

    /**
     * Removes all properties form the object, whose name is not listed in the provided list of expected property names.
     * This does not assert, that the list of expected properties is actually present! It only removes
     * unexpected properties. In order to assert that a list of expected properties is present, please use the
     * assertPropertiesExist() method
     *
     * @param string[] $propertyNameWhitelist Accept these property names
     * @param bool $preserveEncounteredProperties Adds all property names that have already been directly asserted
     *                                            for to the whitelist
     *
     * @return $this
     * @see assertPropertiesExist()
     */
    public function removeSurplusProperties(array $propertyNameWhitelist = [], bool $preserveEncounteredProperties = true): static
    {
        self::assertValueIsListOfPropertyNames($propertyNameWhitelist);
        $this->assertionList[] = static function (ResultBuilder $builder, array $expectedProperties)
        use ($propertyNameWhitelist, $preserveEncounteredProperties): void {
            if ($preserveEncounteredProperties)
                $propertyNameWhitelist = [...$propertyNameWhitelist, ...$expectedProperties];

            $newObject = new \stdClass();
            foreach ($builder->getValue() as $propertyName => $propertyValue) {
                if (in_array($propertyName, $propertyNameWhitelist, true)) {
                    $newObject->$propertyName = $propertyValue;
                }
            }
            $builder->setValue('removed unknown properties', $newObject);
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'parse stdClass';
    }
}
