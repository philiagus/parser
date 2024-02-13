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
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\ArrayKey;
use Philiagus\Parser\Subject\ArrayKeyValuePair;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Subject\MetaInformation;
use Philiagus\Parser\Util\Debug;

class AssertArray extends Base\Parser
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
     * Instructs the parser to hand every value in the array in sequence to the provided parser
     * The provided parser will be called once for every element of the array
     *
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveEachValue(ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            foreach ($builder->getValue() as $key => $value) {
                $builder->unwrapResult(
                    $parser->parse(
                        new ArrayValue($builder->getSubject(), $key, $value)
                    )
                );
            }
        };

        return $this;
    }

    /**
     * Loops through all elements of the array and calls the provided parser once with each combination.
     * The value provided to the parser is an array with two elements: [<key>, <value>]
     *
     * @param ParserContract $parser
     * @return $this
     */
    public function giveEachEntry(ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            foreach ($builder->getValue() as $key => $value) {
                $builder->unwrapResult(
                    $parser->parse(
                        new ArrayKeyValuePair($builder->getSubject(), $key, $value)
                    )
                );
            }
        };

        return $this;
    }

    /**
     * Gives each key of the array to the provided parser. The parser is called once
     * per key
     *
     * @param ParserContract $parser
     *
     * @return static
     */
    public function giveEachKey(ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            foreach ($builder->getValue() as $key => $_) {
                $builder->unwrapResult(
                    $parser->parse(
                        new ArrayKey($builder->getSubject(), $key)
                    )
                );
            }
        };

        return $this;
    }

    /**
     * Provides the list of keys to the defined parser as array
     *
     * @param ParserContract $arrayParser
     *
     * @return static
     */
    public function giveKeys(ParserContract $arrayParser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($arrayParser): void {
            $builder->unwrapResult(
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
    public function giveLength(ParserContract $integerParser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($integerParser): void {
            $builder->unwrapResult(
                $integerParser->parse(
                    new MetaInformation($builder->getSubject(), 'length', count($builder->getValue()))
                )
            );
        };

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * In case the key does not exist an error with the specified message is generated
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
    public function giveValue(
        int|string $key, ParserContract $parser,
        string     $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'
    ): static
    {
        $key = self::normalizeArrayKey($key);
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys) use ($key, $parser, $missingKeyExceptionMessage): void {
            $value = $builder->getValue();
            if (!array_key_exists($key, $value)) {
                $builder->logErrorUsingDebug($missingKeyExceptionMessage, ['key' => $key]);

                return;
            }
            $targetedKeys[] = $key;
            $builder->unwrapResult(
                $parser->parse(new ArrayValue($builder->getSubject(), $key, $value[$key]))
            );
        };

        return $this;
    }

    /**
     * Takes a value and tries to convert it to its matching array key
     * Integers stay unchanged, strings are converted to integer if they contain only that integer with
     * no leading zeroes, otherwise strings are unchanged
     * Every other value leads to a ParserConfigurationException
     *
     * @param mixed $key
     *
     * @return string|int
     */
    protected function normalizeArrayKey(mixed $key): string|int
    {
        if (is_int($key))
            return $key;
        if (!is_string($key))
            throw new ParserConfigurationException("Array keys can only be int or string");
        if (preg_match('/^[1-9]\d*$/', $key))
            return (int)$key;

        return $key;
    }

    /**
     * Performs a parser on the value of a key or the $default if the given key does not exist
     * in the array
     *
     * @param int|string $key
     * @param mixed $default
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function giveDefaultedValue(int|string $key, mixed $default, ParserContract $parser): static
    {
        $key = self::normalizeArrayKey($key);
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys) use ($key, $default, $parser): void {
            $value = $builder->getValue();
            $keyExists = array_key_exists($key, $value);
            if ($keyExists) {
                $keyValue = $value[$key];
                $targetedKeys[] = $key;
            } else {
                $keyValue = $default;
            }
            $builder->unwrapResult(
                $parser->parse(
                    new ArrayValue(
                        $builder->getSubject(),
                        $key,
                        $keyValue
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
     * @param string $errorMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function assertSequentialKeys(string $errorMessage = 'The array is not a sequential numerical array starting at 0'): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($errorMessage): void {
            if (!array_is_list($builder->getValue())) {
                $builder->logErrorUsingDebug($errorMessage);
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
    public function giveOptionalValue(int|string $key, ParserContract $parser): static
    {
        $key = self::normalizeArrayKey($key);
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys) use ($key, $parser): void {
            $value = $builder->getValue();
            if (array_key_exists($key, $value)) {
                $targetedKeys[] = $key;
                $builder->unwrapResult(
                    $parser->parse(
                        new ArrayValue($builder->getSubject(), $key, $value[$key])
                    )
                );
            }
        };

        return $this;
    }

    /**
     * Asserts that the defined list of keys exist in the array. This method ignores surplus keys.
     *
     * If you want to make sure that no surplus keys exist in the array, please use assertNoSurplusKeysExist()
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - key: The key that was found missing
     *
     * @param array<int|string> $expectedKeys
     * @param string $missingKeyMassage
     *
     * @return $this
     * @see assertNoSurplusKeysExist()
     * @see Debug::parseMessage()
     */
    public function assertKeysExist(
        array  $expectedKeys,
        string $missingKeyMassage = 'Array is missing the key {key}'
    ): static
    {
        $normalizedKeys = [];
        foreach ($expectedKeys as $key) {
            $normalizedKeys[] = self::normalizeArrayKey($key);
        }
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys) use ($normalizedKeys, $missingKeyMassage): void {
            $value = $builder->getValue();

            foreach ($normalizedKeys as $key) {
                if (!array_key_exists($key, $value)) {
                    $builder->logErrorUsingDebug($missingKeyMassage, ['key' => $key]);
                } else {
                    $targetedKeys[] = $key;
                }
            }
        };

        return $this;
    }

    /**
     * Asserts that the array does not contain an unexpected keys. This method does not assert that
     * the provided keys do actually exist. It only makes sure, that no key not listed in the provided
     * list of keys exists. In order to check that all required keys exist, please use the
     * assertKeysExists() method
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - key: The key that was found missing
     *
     * @param array<int|string> $expectedKeys
     * @param bool $expectAlreadyTargetedKeys If true the list of already touched array keys are added
     *                                        to the list of expected keys
     * @param string $surplusKeyMessage
     *
     * @return $this
     * @see assertKeysExist()
     * @see Debug::parseMessage()
     */
    public function assertNoSurplusKeysExist(
        array  $expectedKeys = [],
        bool   $expectAlreadyTargetedKeys = true,
        string $surplusKeyMessage = 'Array contains unexpected key {key}'
    ): static
    {
        $normalizedKeys = [];
        foreach ($expectedKeys as $key)
            $normalizedKeys[] = self::normalizeArrayKey($key);

        $this->assertionList[] = static function (ResultBuilder $builder, array $targetedKeys)
        use ($normalizedKeys, $expectAlreadyTargetedKeys, $surplusKeyMessage): void {
            $value = $builder->getValue();

            if ($expectAlreadyTargetedKeys) {
                $normalizedKeys = [...$normalizedKeys, ...$targetedKeys];
            }

            foreach (array_keys($value) as $key) {
                if (!in_array($key, $normalizedKeys)) {
                    $builder->logErrorUsingDebug($surplusKeyMessage, ['key' => $key]);
                }
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        if (!is_array($builder->getValue())) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }
        $targetedKeys = [];
        foreach ($this->assertionList as $assertion) {
            $assertion($builder, $targetedKeys);
        }

        return $builder->createResultWithCurrentValue();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert array';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not an array';
    }
}
