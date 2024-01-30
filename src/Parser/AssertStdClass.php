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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Subject\PropertyName;
use Philiagus\Parser\Subject\PropertyValue;
use Philiagus\Parser\Util\Debug;

class AssertStdClass extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var \SplDoublyLinkedList<\Closure> */
    protected \SplDoublyLinkedList $assertionList;

    final private function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Tests that the property exists and performs the parser on the value if present
     * In case the property does not exist an exception with the specified message is thrown.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - property: The missing property as defined here
     *
     * @param string $property
     * @param ParserContract $parser
     * @param string $missingPropertyExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function givePropertyValue(
        string $property, ParserContract $parser,
        string $missingPropertyExceptionMessage = 'The object does not contain the requested property {property}'
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($property, $parser, $missingPropertyExceptionMessage): void {
            $value = $builder->getValue();
            if (property_exists($value, $property)) {
                $builder->incorporateResult(
                    $parser->parse(
                        new PropertyValue($builder->getSubject(), $property, $value->$property)
                    )
                );
            } else {
                $builder->logErrorUsingDebug(
                    $missingPropertyExceptionMessage,
                    ['property' => $property]
                );
            }
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
    public function giveOptionalPropertyValue(string $property, ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($property, $parser): void {
            $value = $builder->getValue();
            if (property_exists($value, $property)) {
                $builder->incorporateResult(
                    $parser->parse(
                        new PropertyValue($builder->getSubject(), $property, $value->$property)
                    )
                );
            }
        };

        return $this;
    }

    /**
     * Provides the value of the specified property to the parser. If the object does not contain the
     * property the default value is provided to the parser instead
     *
     * @param string $property
     * @param mixed $default
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveDefaultedPropertyValue(string $property, mixed $default, ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($property, $default, $parser): void {
            $value = $builder->getValue();
            $propertyValue = property_exists($value, $property) ? $value->$property : $default;
            $builder->incorporateResult(
                $parser->parse(
                    new PropertyValue($builder->getSubject(), $property, $propertyValue)
                )
            );
        };

        return $this;
    }

    /**
     * Gives the name of every property in the object to the provided parser. That means that the parser
     * is called once per property in the object
     *
     * @param ParserContract $arrayParser
     *
     * @return $this
     */
    public function givePropertyNames(ParserContract $arrayParser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($arrayParser): void {
            $properties = [];
            foreach ($builder->getValue() as $property => $_) {
                $properties[] = $property;
            }
            $builder->incorporateResult(
                $arrayParser->parse(
                    new MetaInformation($builder->getSubject(), 'property names', $properties)
                )
            );
        };

        return $this;
    }

    /**
     * Provides every the value of every property in the object to the specified parser
     *
     * @param ParserContract $arrayParser
     *
     * @return $this
     */
    public function givePropertyValues(ParserContract $arrayParser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($arrayParser): void {
            $propertyValues = [];
            foreach ($builder->getValue() as $propertyValue) {
                $propertyValues[] = $propertyValue;
            }
            $builder->incorporateResult(
                $arrayParser->parse(
                    new MetaInformation($builder->getSubject(), 'property values', $propertyValues)
                )
            );
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
    public function giveEachPropertyName(ParserContract $stringParser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($stringParser): void {
            foreach ($builder->getValue() as $property => $_) {
                $builder->incorporateResult(
                    $stringParser->parse(
                        new PropertyName($builder->getSubject(), $property)
                    )
                );
            }
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
    public function giveEachPropertyValue(ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            foreach ($builder->getValue() as $property => $value) {
                $builder->incorporateResult(
                    $parser->parse(
                        new PropertyValue($builder->getSubject(), $property, $value)
                    )
                );
            }
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
    public function givePropertyCount(ParserContract $integerParser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($integerParser): void {
            $count = 0;
            foreach ($builder->getValue() as $_) {
                $count++;
            }
            $builder->incorporateResult(
                $integerParser->parse(
                    new MetaInformation($builder->getSubject(), 'property count', $count)
                )
            );
        };

        return $this;
    }

    /**
     * Asserts that the defined list of properties exist in the object. This method ignores surplus properties.
     * If you want to make sure that no surplus properties exist in the object, please use assertNoSurplusPropertiesExist()
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - property: The name of the missing property
     *
     * @param string[] $expectedPropertyNames
     * @param string $missingPropertyMassage
     *
     * @return $this
     * @see assertNoSurplusPropertiesExist()
     */
    public function assertPropertiesExist(
        array  $expectedPropertyNames = [],
        string $missingPropertyMassage = 'Object is missing the property {property}'
    ): static
    {
        self::assertValueIsListOfPropertyNames($expectedPropertyNames);
        $this->assertionList[] = static function (ResultBuilder $builder) use ($expectedPropertyNames, $missingPropertyMassage): void {
            $existingProperties = self::extractPropertyNames($builder->getValue());

            foreach ($expectedPropertyNames as $propertyName) {
                if (!in_array($propertyName, $existingProperties)) {
                    $builder->logErrorUsingDebug($missingPropertyMassage, ['property' => $propertyName]);
                }
            }
        };

        return $this;
    }

    /**
     * @param array $list
     *
     * @return void
     * @throws ParserConfigurationException
     */
    protected static function assertValueIsListOfPropertyNames(array $list): void
    {
        foreach ($list as $element) {
            if (!is_string($element)) {
                throw new ParserConfigurationException("Property names must be provided as string");
            }
        }
    }

    /**
     * Provides the list of object property names
     *
     * @param \stdClass $object
     *
     * @return string[]
     */
    protected static function extractPropertyNames(\stdClass $object): array
    {
        $result = [];
        foreach ($object as $key => $_) {
            $result[] = $key;
        }

        return $result;
    }

    /**
     * Asserts that the object does not contain an unexpected properties. This method does not assert that
     * the provided properties do actually exist. It only makes sure, that no property not listed in the provided
     * list of property names exists. In order to check that all required properties exist, please use the
     * assertPropertiesExist() method
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - property: The name of the surplus property
     *
     * @param string[] $expectedPropertyNames
     * @param string $surplusPropertyMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     * @see assertPropertiesExist()
     */
    public function assertNoSurplusPropertiesExist(
        array  $expectedPropertyNames = [],
        string $surplusPropertyMessage = 'Object contains unexpected property {property}'
    ): static
    {
        self::assertValueIsListOfPropertyNames($expectedPropertyNames);
        $this->assertionList[] = static function (ResultBuilder $builder) use ($expectedPropertyNames, $surplusPropertyMessage): void {
            $existingProperties = self::extractPropertyNames($builder->getValue());

            foreach ($existingProperties as $propertyName) {
                if (!in_array($propertyName, $expectedPropertyNames)) {
                    $builder->logErrorUsingDebug($surplusPropertyMessage, ['property' => $propertyName]);
                }
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        if ($builder->getValue() instanceof \stdClass) {
            foreach ($this->assertionList as $assertion) {
                $assertion($builder);
            }
        } else {
            $this->logTypeError($builder);
        }

        return $builder->createResultWithCurrentValue();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not an instance of \stdClass';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert stdClass';
    }
}
