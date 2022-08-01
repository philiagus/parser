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

namespace Philiagus\Parser\Test\ParserTestBase\Argument;

use Generator;
use Philiagus\Parser\Test\ParserTestBase\Argument;
use Philiagus\Parser\Test\ParserTestBase\ErrorCollection;
use Philiagus\Parser\Util\Debug;

class Evaluated implements Argument
{

    private const TYPE_SUCCESS = 1, TYPE_ERROR = 2, TYPE_EXCEPTION = 3;

    private array $cases = [];
    private string $expectErrorMessage;

    public function success(\Closure $generator, \Closure $eligible = null, string $description = null): self
    {
        $description ??= count($this->cases);
        $this->cases[$description] = [
            'type' => self::TYPE_SUCCESS,
            'generator' => $generator,
            'eligible' => $eligible ?? fn() => true,
        ];

        return $this;
    }

    public function error(
        \Closure $generator,
        \Closure $eligible = null,
        string   $description = null
    ): self
    {
        $description ??= count($this->cases);
        $this->cases[$description] = [
            'type' => self::TYPE_ERROR,
            'generator' => $generator,
            'eligible' => $eligible ?? fn() => true,
        ];

        return $this;
    }

    public function expectErrorMessageOnError(string $message): self
    {
        $this->expectErrorMessage = $message;

        return $this;
    }

    public function generate(mixed $subjectValue, array $generatedArgs, array $successes): Generator
    {
        foreach ($this->cases as $name => ['type' => $type, 'generator' => $generator, 'eligible' => $eligible]) {
            if ($eligible($subjectValue)) {
                $value = $generator($subjectValue);
                $name = '#' . $name . ' ' . Debug::stringify($value);
                yield $name => [
                    $type === self::TYPE_SUCCESS,
                    function (array $generatedArguments, array $successStack, ErrorCollection $errorCollection = null) use ($value, $type) {
                        if ($type === self::TYPE_ERROR && isset($this->expectErrorMessage)) {
                            $errorCollection?->add($this->expectErrorMessage);
                        } elseif($type === self::TYPE_EXCEPTION) {
                            $errorCollection?->expectConfigException();
                        }

                        return $value;
                    },
                ];
            }
        }
    }

    public function getErrorMeansFail(): bool
    {
        return true;
    }

    public function configException(\Closure $generator, \Closure $eligible = null, string $description = null): self
    {
        $description ??= count($this->cases);
        $this->cases[$description] = [
            'type' => self::TYPE_EXCEPTION,
            'generator' => $generator,
            'eligible' => $eligible ?? fn() => true,
        ];

        return $this;
    }
}
