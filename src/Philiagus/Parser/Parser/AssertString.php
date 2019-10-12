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
use Philiagus\Parser\Exception\ParsingException;

class AssertString extends Parser
{

    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of type string';

    /**
     * @var null|Parser
     */
    private $length = null;

    /**
     * @var mixed[]
     */
    private $substring = [];

    /**
     * Defines the exception message to use if the value is not a string
     *
     * @param string $message
     *
     * @return $this
     */
    public function withTypeExceptionMessage(string $message): self
    {
        $this->typeExceptionMessage = $message;

        return $this;
    }

    /**
     * Executes strlen on the string and hands the result over to the parser
     *
     * @param Parser $integerParser
     *
     * @return $this
     */
    public function withLength(Parser $integerParser): self
    {
        $this->length = $integerParser;

        return $this;
    }

    /**
     * Performs substr on the string and executes the parser on that part of the string
     *
     * @param int $start
     * @param int|null $length
     * @param Parser $stringParser
     *
     * @return $this
     */
    public function withSubstring(
        int $start,
        ?int $length,
        Parser $stringParser
    ): self
    {
        $this->substring[] = [$start, $length, $stringParser];

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_string($value)) {
            throw new ParsingException($value, $this->typeExceptionMessage, $path);
        }

        if ($this->length) {
            $this->length->parse(strlen($value), $path->meta('length'));
        }

        if ($this->substring) {
            /**
             * @var int $start
             * @var int|null $length
             * @var Parser $parser
             */
            foreach ($this->substring as [$start, $length, $parser]) {
                if ($value === '') {
                    $part = '';
                } else {
                    $part = (string)substr($value, $start, $length);
                }
                $parser->parse($part, $path->meta("$start:$length"));
            }
        }

        return $value;
    }
}