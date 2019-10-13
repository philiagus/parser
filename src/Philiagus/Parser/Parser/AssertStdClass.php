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
use stdClass;

class AssertStdClass
    extends Parser
{

    private $typeExceptionMessage = 'Provided value is not an instance of \stdClass';

    /**
     * @var array[]
     */
    private $withProperty = [];

    /**
     * @var Parser[]
     */
    private $withDefaultedProperty = [];

    public function withTypeExceptionMessage(string $message): self
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
     * @param $property
     * @param Parser $value
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     */
    public function withProperty($property, Parser $value, string $missingKeyExceptionMessage = 'The object does not contain the requested property {property}'): self
    {
        $this->withProperty[$property] = [$value, $missingKeyExceptionMessage];

        return $this;
    }

    /**
     * @param string $property
     * @param $default
     * @param Parser $parser
     *
     * @return $this
     */
    public function withDefaultedProperty(string $property, $default, Parser $parser): self
    {
        $this->withDefaultedProperty[$property] = [$default, $parser];

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_object($value) || get_class($value) !== stdClass::class) {
            throw new Exception\ParsingException($value, $this->typeExceptionMessage, $path);
        }

        $properties = array_keys((array) $value);

        /**
         * @var string|int $property
         * @var Parser $parser
         * @var string $exceptionMessage
         */
        foreach ($this->withProperty as $property => [$parser, $exceptionMessage]) {
            if (!in_array($property, $properties)) {
                throw new ParsingException(
                    $value,
                    strtr($exceptionMessage, ['{property}' => var_export($property, true)]),
                    $path
                );
            }

            $parser->parse($value->{$property}, $path->property((string) $property));
        }

        foreach ($this->withDefaultedProperty as $property => [$element, $parser]) {
            if (in_array($property, $properties)) {
                $element = $value->{$property};
            }

            $parser->parse($element, $path->property((string) $property));
        }

        return $value;
    }
}