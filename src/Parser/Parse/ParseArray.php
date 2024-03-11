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

namespace Philiagus\Parser\Parser\Parse;

use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Parser\Assert\AssertArray;
use Philiagus\Parser\Subject\ArrayKey;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Util\Stringify;

/**
 * Parser to not only validate an array but also alter it. This parser is an extension of the
 * Assert Array parser and allows to change individual values rather than just look at them.
 *
 * @package Parser\Parse
 * @target-type array
 */
class ParseArray extends AssertArray
{

    /**
     * Every value of the array is provided to the provided parser. The value of the property
     * will be overwritten with the result of the parser
     *
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function modifyEachValue(ParserContract $parser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            $array = $builder->getValue();
            foreach ($array as $key => &$value) {
                $value = $builder->unwrapResult(
                    $parser->parse(new ArrayValue($builder->getSubject(), $key, $value)),
                    $value
                );
            }

            $builder->setValue('modify each value', $array);
        };

        return $this;
    }

    /**
     * Changes every key in the array using the parser. The parser must result in a string or integer, otherwise
     * an Error is created with the $newKeyIsNotUsableMessage
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - oldKey: The key before parsing it
     * - newKey: The key after parsing
     *
     * @param ParserContract $parser
     * @param string $newKeyIsNotUsableMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     */
    public function modifyEachKey(
        ParserContract $parser,
        string         $newKeyIsNotUsableMessage = 'A parser resulted in an invalid array key for key {oldKey.raw}'
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$expectedKeys) use ($parser, $newKeyIsNotUsableMessage): void {
            $array = $builder->getValue();
            $result = [];
            $expectedKeys = [];
            foreach ($array as $key => $value) {
                $newKeyResult = $parser->parse(new ArrayKey($builder->getSubject(), $key));
                if (!$newKeyResult->isSuccess()) {
                    $builder->unwrapResult($newKeyResult);

                    continue;
                }
                $newKey = $newKeyResult->getValue();
                if (!is_int($newKey) && !is_string($newKey)) {
                    $builder->logErrorStringify(
                        $newKeyIsNotUsableMessage,
                        ['oldKey' => $key, 'newKey' => $newKey]
                    );
                } else {
                    $expectedKeys[] = $newKey;
                    $result[$newKey] = $value;
                }
            }

            $builder->setValue('modify each key', $result);
        };

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * In case the key does not exist an error with the specified message is generated
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - key: The missing key
     * - value: The value currently being parsed
     *
     *
     * @param string|int $key
     * @param ParserContract $parser
     * @param string $missingKeyErrorMessage
     *
     * @return $this
     * @see Stringify::parseMessage()
     */
    public function modifyValue(string|int $key, ParserContract $parser, string $missingKeyErrorMessage = 'Array does not contain the requested key {key}'): static
    {
        $key = self::normalizeArrayKey($key);
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys) use ($key, $parser, $missingKeyErrorMessage): void {
            $value = $builder->getValue();
            if (!array_key_exists($key, $value)) {
                $builder->logErrorStringify($missingKeyErrorMessage, ['key' => $key]);

                return;
            }
            $targetedKeys[] = $key;

            $result = $parser->parse(
                new ArrayValue($builder->getSubject(), $key, $value[$key])
            );
            if (!$result->isSuccess()) {
                $builder->unwrapResult($result);

                return;
            }
            $value[$key] = $result->getValue();

            $builder->setValue("modify key $key value", $value);
        };

        return $this;
    }

    /**
     * If the provided key does not exist in the array the key is added with the provided value
     *
     * @param int|string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function defaultKey(int|string $key, mixed $value): static
    {
        $key = self::normalizeArrayKey($key);
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys) use ($key, $value): void {
            $array = $builder->getValue();
            $targetedKeys[] = $key;
            if (array_key_exists($key, $array))
                return;

            $array[$key] = $value;
            $builder->setValue("defaulted key '$key", $array);
        };

        return $this;
    }

    /**
     * Unions the provided array with the subject array.
     * This will not overwrite any values, but add missing keys with the defined value
     *
     * @param array $array
     *
     * @return $this
     */
    public function unionWith(array $array): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys) use ($array): void {
            $targetedKeys = [...$targetedKeys, ...array_keys($array)];
            $builder->setValue('array union', $builder->getValue() + $array);
        };

        return $this;
    }

    /**
     * Forces the array to have sequential keys, this is identical to calling array_values on the array
     *
     * @return $this
     */
    public function forceSequentialKeys(): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys): void {
            $targetedKeys = [];
            $builder->setValue(
                'force sequential keys', array_values($builder->getValue())
            );
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
    public function modifyOptionalValue(int|string $key, ParserContract $parser): static
    {
        $key = self::normalizeArrayKey($key);
        $this->assertionList[] = static function (ResultBuilder $builder, array &$targetedKeys) use ($key, $parser): void {
            $value = $builder->getValue();
            if (!array_key_exists($key, $value))
                return;

            $targetedKeys[] = $key;
            $result = $parser->parse(
                new ArrayValue($builder->getSubject(), $key, $value[$key])
            );
            if (!$result->isSuccess()) {
                $builder->unwrapResult($result);

                return;
            }
            $value[$key] = $result->getValue();

            $builder->setValue("modification of key '$key'", $value);
        };

        return $this;
    }

    /**
     * Removes every element form the array, whose key is not listed in the provided list of expected keys.
     * This does not assert, that the list of expected keys is actually present! It only removes
     * unexpected keys. In order to assert that a list of expected keys is present, please use the assertKeysExist()
     * method
     *
     * @param array<int|string> $expectedKeys
     *
     * @return $this
     * @see assertKeysExist()
     */
    public function removeSurplusElements(array $expectedKeys, bool $expectAlreadyTargetedKeys = true): static
    {
        $keys = [];
        foreach ($expectedKeys as $key)
            $keys[] = self::normalizeArrayKey($key);
        $this->assertionList[] = static function (ResultBuilder $builder, array $targetedKeys) use ($keys, $expectAlreadyTargetedKeys): void {
            if($expectAlreadyTargetedKeys) {
                $keys = [...$keys, ...$targetedKeys];
            }
            $newValue = array_intersect_key($builder->getValue(), array_flip($keys));
            $builder->setValue('remove surplus keys', $newValue);
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'parse array';
    }
}
