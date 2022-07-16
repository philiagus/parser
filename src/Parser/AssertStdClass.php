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
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

class AssertStdClass implements Parser
{
    use Chainable, OverwritableParserDescription, TypeExceptionMessage;

    /** @var \SplDoublyLinkedList<\Closure> */
    protected \SplDoublyLinkedList $assertionList;

    final private function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
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
     * @param string $missingPropertyExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function givePropertyValue(
        string $property, ParserContract $parser,
        string $missingPropertyExceptionMessage = 'The object does not contain the requested property {property}'
    ): self
    {
        $this->assertionList[] = function (ResultBuilder $builder) use ($property, $parser, $missingPropertyExceptionMessage): void {
            $value = $builder->getCurrentValue();
            if (property_exists($value, $property)) {
                $builder->incorporateResult(
                    $parser->parse(
                        $builder->subjectPropertyValue($property, $value->$property)
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
     *
     * @inheritdoc
     */
    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        if ($builder->getCurrentValue() instanceof \stdClass) {
            foreach ($this->assertionList as $assertion) {
                $assertion($builder);
            }
        } else {
            $this->logTypeError($builder);
        }

        return $builder->createResultBasedOnCurrentSubject();
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
        $this->assertionList[] = function (ResultBuilder $builder) use ($property, $parser): void {
            $value= $builder->getCurrentValue();
            if (property_exists($value, $property)) {
                $builder->incorporateResult(
                    $parser->parse(
                        $builder->subjectPropertyValue(
                            $property,
                            $value->$property
                        )
                    )
                );
            }
        };

        return $this;
    }

    public function giveDefaultedPropertyValue(string $property, $default, ParserContract $parser): self
    {
        $this->assertionList[] = function (ResultBuilder $builder) use ($property, $default, $parser): void {
            $value = $builder->getCurrentValue();
            $propertyValue = property_exists($value, $property) ? $value->$property : $default;
            $builder->incorporateResult(
                $parser->parse(
                    $builder->subjectPropertyValue($property, $propertyValue)
                )
            );
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
        $this->assertionList[] = function (ResultBuilder $builder) use ($arrayParser): void {
            $properties = [];
            foreach ($builder->getCurrentValue() as $property => $_) {
                $properties[] = $property;
            }
            $builder->incorporateResult(
                $arrayParser->parse(
                    $builder->subjectMeta('property names', $properties)
                )
            );
        };

        return $this;
    }

    public function givePropertyValues(ParserContract $arrayParser): self
    {
        $this->assertionList[] = function (ResultBuilder $builder) use ($arrayParser): void {
            $propertyValues = [];
            foreach ($builder->getCurrentValue() as $propertyValue) {
                $propertyValues[] = $propertyValue;
            }
            $builder->incorporateResult(
                $arrayParser->parse(
                    $builder->subjectMeta('property values', $propertyValues)
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
    public function giveEachPropertyName(ParserContract $stringParser): self
    {
        $this->assertionList[] = function (ResultBuilder $builder) use ($stringParser): void {
            foreach ($builder->getCurrentValue() as $property => $_) {
                $builder->incorporateResult(
                    $stringParser->parse(
                        $builder->subjectPropertyName($property)
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
    public function giveEachPropertyValue(ParserContract $parser): self
    {
        $this->assertionList[] = function (ResultBuilder $builder) use ($parser): void {
            foreach ($builder->getCurrentValue() as $property => $value) {
                $builder->incorporateResult(
                    $parser->parse(
                        $builder->subjectPropertyValue($property, $value)
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
    public function givePropertyCount(ParserContract $integerParser): self
    {
        $this->assertionList[] = function (ResultBuilder $builder) use ($integerParser): void {
            $count = 0;
            foreach ($builder->getCurrentValue()->value as $_) {
                $count++;
            }
            $builder->incorporateResult(
                $integerParser->parse(
                    $builder->subjectMeta('property count', $count)
                )
            );
        };

        return $this;
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not an instance of \stdClass';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'assert stdClass';
    }
}
