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

namespace Philiagus\Parser\Parser\Assert;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Subject\PropertyName;
use Philiagus\Parser\Subject\PropertyNameValuePair;
use Philiagus\Parser\Subject\PropertyValue;
use Philiagus\Parser\Util\Stringify;

/**
 * Asserts that the value is an \stdClass and allows to examine the contents of the \stdClass
 *
 * @package Parser\Assert
 * @target-type \stdClass
 */
class AssertStdClass extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var \SplDoublyLinkedList<\Closure> */
    protected \SplDoublyLinkedList $assertionList;

    protected function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Tests that the property exists and performs the parser on the value if present
     * In case the property does not exist an error with the specified message is generated
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - property: The missing property as defined here
     *
     * @param string $property
     * @param ParserContract $parser
     * @param string $missingPropertyExceptionMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     *
     */
    public function givePropertyValue(
        string $property, ParserContract $parser,
        string $missingPropertyExceptionMessage = 'The object does not contain the requested property {property}'
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedProperties) use ($property, $parser, $missingPropertyExceptionMessage): void {
            $value = $builder->getValue();
            if (property_exists($value, $property)) {
                $targetedProperties[] = $property;
                $builder->unwrapResult(
                    $parser->parse(
                        new PropertyValue($builder->getSubject(), $property, $value->$property)
                    )
                );
            } else {
                $builder->logErrorStringify(
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
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedProperties) use ($property, $parser): void {
            $value = $builder->getValue();
            if (property_exists($value, $property)) {
                $targetedProperties[] = $property;
                $builder->unwrapResult(
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
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedProperties) use ($property, $default, $parser): void {
            $value = $builder->getValue();
            $propertyExists = property_exists($value, $property);
            if ($propertyExists) {
                $targetedProperties[] = $property;
                $propertyValue = $value->$property;
            } else {
                $propertyValue = $default;
            }
            $builder->unwrapResult(
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
            $builder->unwrapResult(
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
            $builder->unwrapResult(
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
                $builder->unwrapResult(
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
                $builder->unwrapResult(
                    $parser->parse(
                        new PropertyValue($builder->getSubject(), $property, $value)
                    )
                );
            }
        };

        return $this;
    }

    /**
     * Loops through all property names and values and calls the provided parser once with each combination.
     * The value provided to the parser is an array with two elements: [<property name>, <property value>]
     *
     * @param ParserContract $parser
     * @return $this
     */
    public function giveEachEntry(ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            foreach ($builder->getValue() as $property => $value) {
                $builder->unwrapResult(
                    $parser->parse(
                        new PropertyNameValuePair($builder->getSubject(), $property, $value)
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
            foreach ($builder->getValue() as $ignored)
                $count++;
            $builder->unwrapResult(
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
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
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
        $this->assertionList[] = static function (ResultBuilder $builder, array &$expectedProperties) use ($expectedPropertyNames, $missingPropertyMassage): void {
            $existingProperties = self::extractPropertyNames($builder->getValue());
            foreach ($expectedPropertyNames as $propertyName) {
                if (!in_array($propertyName, $existingProperties)) {
                    $expectedProperties[] = $propertyName;
                    $builder->logErrorStringify($missingPropertyMassage, ['property' => $propertyName]);
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
        foreach ($object as $key => $_)
            $result[] = $key;

        return $result;
    }

    /**
     * Asserts that the object does not contain an unexpected properties. This method does not assert that
     * the provided properties do actually exist. It only makes sure, that no property not listed in the provided
     * list of property names exists. In order to check that all required properties exist, please use the
     * assertPropertiesExist() method
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - property: The name of the surplus property
     *
     * @param array $expectedPropertyNames List of property names to assert for
     * @param bool $expectAlreadyTouchedNames If true the list of already touched
     *                                        property names are added to the list of expected
     * @param string $surplusPropertyMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     * @see assertPropertiesExist()
     */
    public function assertNoSurplusPropertiesExist(
        array  $expectedPropertyNames = [],
        bool   $expectAlreadyTouchedNames = true,
        string $surplusPropertyMessage = 'Object contains unexpected property {property}'
    ): static
    {
        self::assertValueIsListOfPropertyNames($expectedPropertyNames);
        $this->assertionList[] = static function (ResultBuilder $builder, array $expectedProperties)
        use ($expectedPropertyNames, $expectAlreadyTouchedNames, $surplusPropertyMessage): void {
            if ($expectAlreadyTouchedNames) {
                $expectedPropertyNames = [...$expectedPropertyNames, ...$expectedProperties];
            }

            foreach (self::extractPropertyNames($builder->getValue()) as $propertyName) {
                if (!in_array($propertyName, $expectedPropertyNames)) {
                    $builder->logErrorStringify($surplusPropertyMessage, ['property' => $propertyName]);
                }
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Result
    {
        $expectedProperties = [];
        if ($builder->getValue() instanceof \stdClass) {
            foreach ($this->assertionList as $assertion) {
                $assertion($builder, $expectedProperties);
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
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'assert stdClass';
    }
}
