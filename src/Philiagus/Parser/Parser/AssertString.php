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
use Philiagus\Parser\Util\Debug;

class AssertString extends Parser
{

    /**
     * @var string
     */
    private $typeExceptionMessage = 'Provided value is not of type string';

    /**
     * @var callable[]
     */
    private $assertionList = [];

    /**
     * Defines the exception message to use if the value is not a string
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function overwriteTypeExceptionMessage(string $message): self
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
        $this->assertionList[] = function (string $value, Path $path) use ($integerParser) {
            $integerParser->parse(strlen($value), $path->meta('length'));
        };

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
        $this->assertionList[] = function (string $value, Path $path) use ($start, $length, $stringParser) {
            if ($value === '') {
                $part = '';
            } else {
                $part = (string) substr($value, $start, $length);
            }
            $stringParser->parse($part, $path->meta("$start:$length"));
        };

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute($value, Path $path)
    {
        if (!is_string($value)) {
            throw new ParsingException(
                $value,
                Debug::parseMessage($this->typeExceptionMessage, ['value' => $value]),
                $path
            );
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($value, $path);
        }

        return $value;
    }
}