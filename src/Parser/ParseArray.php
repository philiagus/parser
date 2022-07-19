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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

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
            $array = $builder->getCurrentValue();
            foreach ($array as $key => &$value) {
                $value = $builder->incorporateResult(
                    $parser->parse($builder->subjectArrayValue($key, $value)),
                    $value
                );
            }

            $builder->setCurrentValue('modfiy each value', $array);
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
        string $newKeyIsNotUsableMessage = 'A parser resulted in an invalid array key for key {oldKey.raw}'
    ): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($parser, $newKeyIsNotUsableMessage): void {
            $array = $builder->getCurrentValue();
            $result = [];
            foreach ($array as $key => $value) {
                $newKeyResult = $parser->parse($builder->subjectArrayKey($key));
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

            $builder->setCurrentValue('modify each key', $result);
        };

        return $this;
    }

    /**
     * Tests that the key exists and performs the parser on the value if present
     * If the key does not exist an exception with the specified message is thrown
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
            $value = $builder->getCurrentValue();
            if (!array_key_exists($key, $value)) {
                $builder->logErrorUsingDebug($missingKeyExceptionMessage, ['key' => $key]);

                return;
            }

            $result = $parser->parse(
                $builder->subjectArrayValue($key, $value[$key])
            );
            if (!$result->isSuccess()) {
                $builder->incorporateResult($result);

                return;
            }
            $value[$key] = $result->getValue();

            $builder->setCurrentValue("modify key {$key} value", $value);
        };

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function defaultKey(int|string $key, $value): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder) use ($key, $value): void {
            $array = $builder->getCurrentValue();
            if (array_key_exists($key, $array)) {
                return;
            }

            $array[$key] = $value;

            $builder->setCurrentValue("defaulted key '$key", $array);
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
            $builder->setCurrentValue('array union', $builder->getCurrentValue() + $array);
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
            $builder->setCurrentValue(
                'force sequential keys', array_values($builder->getCurrentValue())
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
            $value = $builder->getCurrentValue();
            if (!array_key_exists($key, $value)) {
                return;

            }
            $result = $parser->parse(
                $builder->subjectArrayValue($key, $value[$key])
            );
            if (!$result->isSuccess()) {
                $builder->incorporateResult($result);

                return;
            }
            $value[$key] = $result->getValue();

            $builder->setCurrentValue("modification of key '$key'", $value);
        };

        return $this;
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'parse array';
    }
}
