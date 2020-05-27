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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Exception\ParsingException;

class AssertStdClass
    extends Parser
{

    private $typeExceptionMessage = 'Provided value is not an instance of \stdClass';

    /**
     * @var callable[]
     */
    private $assertionList = [];

    /**
     * Defines the exception message if the provided value is not an instance of \stdClass
     *
     * @param string $message
     *
     * @return $this
     */
    public function overwriteTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * If the key does not exist an exception with the specified message is thrown.
     * Replacers in the exception message:
     * {key} = var_export($key, true)
     *
     * @param string $property
     * @param Parser $parser
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     */
    public function withProperty(string $property, Parser $parser, string $missingKeyExceptionMessage = 'The object does not contain the requested property {property}'): self
    {
        $this->assertionList[] = function (\stdClass $value, array $properties, Path $path) use ($property, $parser, $missingKeyExceptionMessage) {
            if (!in_array($property, $properties)) {
                throw new ParsingException(
                    $value,
                    strtr($missingKeyExceptionMessage, ['{property}' => var_export($property, true)]),
                    $path
                );
            }

            $parser->parse($value->{$property}, $path->property((string) $property));
        };

        return $this;
    }

    /**
     * Puts the value of the property against the defined parser or the default, if the property does not exist
     *
     * @param string $property
     * @param $default
     * @param Parser $parser
     *
     * @return $this
     */
    public function withDefaultedProperty(string $property, $default, Parser $parser): self
    {
        $this->assertionList[] = function (\stdClass $value, array $properties, Path $path) use ($property, $default, $parser) {
            if (in_array($property, $properties)) {
                $element = $value->{$property};
            } else {
                $element = $default;
            }

            $parser->parse($element, $path->property((string) $property));
        };

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_object($value) || get_class($value) !== \stdClass::class) {
            throw new Exception\ParsingException($value, $this->typeExceptionMessage, $path);
        }

        $properties = array_keys((array) $value);
        foreach ($this->assertionList as $assertion) {
            $assertion($value, $properties, $path);
        }

        return $value;
    }
}