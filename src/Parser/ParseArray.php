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
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\ArrayKey;
use Philiagus\Parser\Subject\ArrayValue;
use Philiagus\Parser\Util\Debug;
use Philiagus\Parser\Contract;

class ParseArray extends AssertArray
{

    /**
     * @param ParserContract $parser
     *
     * @return $this
     */
    public function modifyEachValue(ParserContract $parser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser): void {
            $array = $builder->getValue();
            foreach ($array as $key => &$value) {
                $value = $builder->incorporateResult(
                    $parser->parse(new ArrayValue($builder->getSubject(), $key, $value)),
                    $value
                );
            }

            $builder->setValue('modify each value', $array);
        };

        return $this;
    }

    /**
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - oldKey: The key before parsing it
     * - newKey: The key after parsing
     *
     * @param ParserContract $parser
     * @param string $newKeyIsNotUsableMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function modifyEachKey(
        ParserContract $parser,
        string         $newKeyIsNotUsableMessage = 'A parser resulted in an invalid array key for key {oldKey.raw}'
    ): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser, $newKeyIsNotUsableMessage): void {
            $array = $builder->getValue();
            $result = [];
            foreach ($array as $key => $value) {
                $newKeyResult = $parser->parse(new ArrayKey($builder->getSubject(), $key));
                if (!$newKeyResult->isSuccess()) {
                    $builder->incorporateResult($newKeyResult);

                    continue;
                }
                $newKey = $newKeyResult->getValue();
                if (!is_int($newKey) && !is_string($newKey)) {
                    $builder->logErrorUsingDebug(
                        $newKeyIsNotUsableMessage,
                        ['oldKey' => $key, 'newKey' => $newKey]
                    );
                } else {
                    $result[$newKey] = $value;
                }
            }

            $builder->setValue('modify each key', $result);
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
     * @param string|int $key
     * @param ParserContract $parser
     * @param string $missingKeyExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     */
    public function modifyValue(string|int $key, ParserContract $parser, string $missingKeyExceptionMessage = 'Array does not contain the requested key {key}'): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($key, $parser, $missingKeyExceptionMessage): void {
            $value = $builder->getValue();
            if (!array_key_exists($key, $value)) {
                $builder->logErrorUsingDebug($missingKeyExceptionMessage, ['key' => $key]);

                return;
            }

            $result = $parser->parse(
                new ArrayValue($builder->getSubject(), $key, $value[$key])
            );
            if (!$result->isSuccess()) {
                $builder->incorporateResult($result);

                return;
            }
            $value[$key] = $result->getValue();

            $builder->setValue("modify key {$key} value", $value);
        };

        return $this;
    }

    /**
     * @param int|string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function defaultKey(int|string $key, mixed $value): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($key, $value): void {
            $array = $builder->getValue();
            if (array_key_exists($key, $array)) {
                return;
            }

            $array[$key] = $value;

            $builder->setValue("defaulted key '$key", $array);
        };

        return $this;
    }

    /**
     * @param array $array
     *
     * @return $this
     */
    public function unionWith(array $array): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($array): void {
            $builder->setValue('array union', $builder->getValue() + $array);
        };

        return $this;
    }

    /**
     * Forces the array to have sequential keys, this is identical to calling array_values on the array
     *
     * @return $this
     */
    public function forceSequentialKeys(): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder): void {
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
    public function modifyOptionalValue(int|string $key, ParserContract $parser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($key, $parser): void {
            $value = $builder->getValue();
            if (!array_key_exists($key, $value)) {
                return;

            }
            $result = $parser->parse(
                new ArrayValue($builder->getSubject(), $key, $value[$key])
            );
            if (!$result->isSuccess()) {
                $builder->incorporateResult($result);

                return;
            }
            $value[$key] = $result->getValue();

            $builder->setValue("modification of key '$key'", $value);
        };

        return $this;
    }

    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'parse array';
    }
}
