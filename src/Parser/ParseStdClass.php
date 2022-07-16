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

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

class ParseStdClass extends AssertStdClass
{

    public function defaultProperty(string $property, $defaultValue): self
    {
        $this->assertionList[] = function (ResultBuilder $builder) use ($property, $defaultValue): void {
            $value = $builder->getCurrentValue();
            if (property_exists($value, $property)) {
                return;
            }
            $value = clone $value;
            $value->$property = $defaultValue;

            $builder->setCurrentSubject(
                $builder->subjectInternal("defaulted property '$property'", $value)
            );
        };

        return $this;
    }

    public function defaultWith(\stdClass $object): self
    {
        $this->assertionList[] = function (ResultBuilder $builder) use ($object): void {
            $value = $builder->getCurrentValue();
            $cloned = false;
            foreach ($object as $property => $newValue) {
                if (!property_exists($value, $property)) {
                    if (!$cloned) {
                        $cloned = true;
                        $value = clone $value;
                    }
                    $value->$property = $newValue;
                }
            }

            if (!$cloned) return;

            $builder->setCurrentSubject(
                $builder->subjectInternal('defaulted with object', $value)
            );
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
        $this->assertionList[] = function (ResultBuilder $builder) use ($property, $parser, $missingKeyExceptionMessage): void {
            $value = $builder->getCurrentValue();
            if (!property_exists($value, $property)) {
                $builder->logErrorUsingDebug(
                    $missingKeyExceptionMessage,
                    ['property' => $property]
                );

                return;
            }

            $result = $parser->parse(
                $builder->subjectPropertyValue($property, $value->$property)
            );
            if ($result->isSuccess()) {
                $value = clone $value;
                $value->$property = $result->getValue();

                $builder->setCurrentSubject(
                    $builder->subjectInternal("modify property '$property'", $value)
                );

                return;
            }

            $builder->incorporateResult($result);
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
        $this->assertionList[] = function (ResultBuilder $builder) use ($property, $parser): void {
            $value = $builder->getCurrentValue();
            if (!property_exists($value, $property)) {
                return;

            }
            $result = $parser->parse(
                $builder->subjectPropertyValue($property, $value->$property)
            );

            if ($result->isSuccess()) {
                $value = clone $value;
                $value->$property = $result->getValue();

                $builder->setCurrentSubject(
                    $builder->subjectInternal("modify property '$property' value", $value)
                );

                return;
            }

            $builder->incorporateResult($result);
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
        $this->assertionList[] = function (ResultBuilder $builder) use ($newPropertyNameIsNotUsableMessage, $stringParser): void {
            $result = new \stdClass();
            $value = $builder->getCurrentValue();
            foreach ($value as $property => $propValue) {
                $newNameResult = $stringParser->parse(
                    $builder->subjectPropertyName($property)
                );
                if ($newNameResult->isSuccess()) {
                    $newName = $newNameResult->getValue();
                    if (!is_string($newName)) {
                        throw new Exception\RuntimeParserConfigurationException(
                            Debug::parseMessage(
                                $newPropertyNameIsNotUsableMessage,
                                ['property' => $property, 'value' => $newName]
                            )
                        );
                    }
                    $result->$newName = $propValue;

                    continue;
                }

                $builder->incorporateResult($newNameResult);

                $result->$property = $propValue;
            }

            $builder->setCurrentSubject(
                $builder->subjectInternal(
                    'modify each property name', $result
                )
            );
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
        $this->assertionList[] = function (ResultBuilder $builder) use ($parser): void {
            $result = new \stdClass();
            foreach ($builder->getCurrentValue() as $property => $value) {
                $newValueResult = $parser->parse(
                    $builder->subjectPropertyValue($property, $value)
                );
                if ($newValueResult->isSuccess()) {
                    $result->$property = $newValueResult->getValue();

                    continue;
                }
                $builder->incorporateResult($newValueResult);
                $result->$property = $value;
            }

            $builder->setCurrentSubject(
                $builder->subjectInternal('modify each property value', $result)
            );
        };

        return $this;
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'parse stdClass';
    }
}
