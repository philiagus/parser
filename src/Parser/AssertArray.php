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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\ArrayKey;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Util\Debug;
use Philiagus\Parser\Contract;

class AssertArray extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var \SplDoublyLinkedList<\Closure> */
    protected \SplDoublyLinkedList $assertionList;

    protected function __construct()
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

    public function giveEachValue(ParserContract $parser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            foreach ($builder->getValue() as $key => $value) {
                $builder->incorporateResult(
                    $parser->parse(
                        new ArrayValue($builder->getSubject(), $key, $value)
                    )
                );
            }
        };

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): \Philiagus\Parser\Contract\Result
    {
        if (!is_array($builder->getValue())) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        foreach ($this->assertionList as $assertion) {
            $assertion($builder);
        }

        return $builder->createResultWithCurrentValue();
    }

    /**
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveEachKey(ParserContract $parser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            foreach ($builder->getValue() as $key => $_) {
                $builder->incorporateResult(
                    $parser->parse(
                        new ArrayKey($builder->getSubject(), $key)
                    )
                );
            }
        };

        return $this;
    }

    /**
     * @param ParserContract $arrayParser
     *
     * @return $this
     */
    public function giveKeys(ParserContract $arrayParser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($arrayParser): void {
            $builder->incorporateResult(
                $arrayParser->parse(
                    new MetaInformation($builder->getSubject(), 'keys', array_keys($builder->getValue()))
                )
            );
        };

        return $this;
    }

    /**
     * Defines a parser that the number of elements in the array gets forwarded to
     *
     * @param ParserContract $integerParser
     *
     * @return $this
     */
    public function giveLength(ParserContract $integerParser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($integerParser): void {
            $builder->incorporateResult(
                $integerParser->parse(
                    new MetaInformation($builder->getSubject(), 'length', count($builder->getValue()))
                )
            );
        };

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * In case the key does not exist an exception with the specified message is thrown
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - key: The missing key
     * - subject: The value currently being parsed
     *
     *
     * @param int|string $key
     * @param ParserContract $parser
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function giveValue(int|string $key, ParserContract $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($key, $parser, $missingKeyExceptionMessage): void {
            $value = $builder->getValue();
            if (!array_key_exists($key, $value)) {
                $builder->logErrorUsingDebug(
                    $missingKeyExceptionMessage,
                    ['key' => $key]
                );

                return;
            }
            $builder->incorporateResult(
                $parser->parse(new ArrayValue($builder->getSubject(), $key, $value[$key]))
            );
        };

        return $this;
    }

    /**
     * Performs a parser on the value of a key or the $default if the given key does not exist
     * in the array
     *
     * @param $key
     * @param $default
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveDefaultedValue(int|string $key, $default, ParserContract $parser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($key, $default, $parser): void {
            $value = $builder->getValue();
            $builder->incorporateResult(
                $parser->parse(
                    new ArrayValue(
                        $builder->getSubject(),
                        $key,
                        array_key_exists($key, $value) ?
                            $value[$key] :
                            $default
                    )
                )
            );
        };

        return $this;
    }

    /**
     * Specifies that this array is expected to have numeric keys starting at 0, incrementing by 1
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $exceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function assertSequentialKeys(string $exceptionMessage = 'The array is not a sequential numerical array starting at 0'): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($exceptionMessage): void {
            if (!array_is_list($builder->getValue())) {
                $builder->logErrorUsingDebug($exceptionMessage);
            }
        };

        return $this;
    }

    /**
     * If the array has the provided key, the value of that key is provided to the parser
     *
     * @param int|string $key
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveOptionalValue(int|string $key, ParserContract $parser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($key, $parser): void {
            $value = $builder->getValue();
            if (array_key_exists($key, $value)) {
                $builder->incorporateResult(
                    $parser->parse(
                        new ArrayValue($builder->getSubject(), $key, $value[$key])
                    )
                );
            }
        };

        return $this;
    }

    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert array';
    }

    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not an array';
    }
}
