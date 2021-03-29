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

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Path\Root;
use Philiagus\Parser\Util\Debug;

abstract class Parser implements ParserContract
{
    /**
     * @var mixed
     */
    private $target;

    /**
     * @var ParserContract[]
     */
    private $then = [];

    /**
     * null = no overwrite set so far
     * string = use this exception
     *
     * @var null|string
     */
    private $parsingExceptionOverwrite = null;

    /**
     * The constructor receives the target to parse into
     *
     * @param mixed $target
     */
    public function __construct(&$target = null)
    {
        $this->target = &$target;
    }

    /**
     * Static version of the constructor for more readable creation when chaining calls
     *
     * @param null $target
     * @return static
     */
    public static function new(&$target = null): self
    {
        return new static($target);
    }

    /**
     * @param mixed $value
     * @param Path|null $path
     *
     * @return mixed
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    final public function parse($value, Path $path = null)
    {
        if ($path === null) {
            $path = new Root('root');
        }

        try {
            $value = $this->execute($value, $path);
            foreach($this->then as $parser) {
                $value = $parser->parse($value, $path);
            }
            return $this->target = $value;
        } catch (ParsingException $e) {
            if ($this->parsingExceptionOverwrite !== null) {
                throw new ParsingException(
                    $value,
                    Debug::parseMessage(
                        $this->parsingExceptionOverwrite,
                        [
                            'value' => $value
                        ]
                    ),
                    $path
                );
            }

            throw $e;
        }
    }

    /**
     * Real conversion of the provided value into the target value
     * This must be individually implemented by the implementing parser class
     *
     * @param mixed $value
     * @param Path $path
     *
     * @return mixed
     * @throws ParsingException
     * @throws ParserConfigurationException
     */
    abstract protected function execute($value, Path $path);

    /**
     * Appends a parser to execute once this parser has done its job
     * The result of this parser is identical to the result of the chained parser
     *
     * If then is called multiple times the parsers are chained to one another
     * The result of this parser is given to the first in the chain, the result of
     * that parser is given to the next in the chain.
     *
     * @param ParserContract $parser
     *
     * @return ParserContract
     */
    public function then(ParserContract $parser): ParserContract
    {
        $this->then[] = $parser;

        return $this;
    }

    /**
     * Allows to overwrite any exception thrown by this or underlying parsers
     * with the defined exception. If null is defined the overwrite is being
     * blocked from being set.
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - value: The value originally provided to this parser
     *
     * @param string|null $message
     *
     * @return $this
     */
    public function setParsingExceptionOverwrite(?string $message): self
    {
        $this->parsingExceptionOverwrite = $message;

        return $this;
    }

}