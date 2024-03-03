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
use Philiagus\Parser\Util\Stringify;

class Message implements Argument
{

    private array $eligible = [];
    private array $parameterElements = [];
    private array $fixedElements = [];

    private ?\Closure $generatedElements = null;

    public function __construct()
    {
    }

    public function withParameterElement(string $name, int $parameter): self
    {
        $this->parameterElements[$name] = $parameter;

        return $this;
    }

    public function expectedWhen(\Closure $when): self
    {
        $this->eligible[] = $when;

        return $this;
    }

    public function generate(mixed $subjectValue, array $generatedArgs, array $successes): Generator
    {
        yield 'without replacers' => [
            true,
            function (array $generatedArguments, array $successStack, ErrorCollection $errorCollection = null) use ($subjectValue) {
                $eligible = array_reduce(
                    $this->eligible,
                    fn(bool $carry, \Closure $eligible) => $carry && $eligible($subjectValue, $generatedArguments, $successStack),
                    true
                );
                if ($eligible) {
                    $count = 1;
                    if ($this->generatedElements) {
                        $count = count(($this->generatedElements)($subjectValue, $generatedArguments, $successStack));
                    }
                    for (; $count > 0; $count--) {
                        $errorCollection?->add('=', 'MESSAGE WITHOUT REPLACERS');
                    }
                }

                return 'MESSAGE WITHOUT REPLACERS';
            },
        ];

        yield 'with replacers' => [
            true,
            function (array $generatedArguments, array $successStack, ErrorCollection $errorCollection = null) use ($subjectValue) {
                $generated = null;
                if ($this->generatedElements) {
                    $generated = ($this->generatedElements)($subjectValue, $generatedArguments, $successStack);
                }
                if (empty($generated)) {
                    $generated = [[]];
                }
                $eligible = array_reduce(
                    $this->eligible,
                    fn(bool $carry, \Closure $eligible) => $carry && $eligible($subjectValue, $generatedArguments, $successStack),
                    true
                );
                $message = '{subject.debug} ';
                foreach ($generated as $generatedGroup) {
                    $message = '{subject.debug} ';
                    $replacers = ['subject' => $subjectValue];
                    foreach ($this->parameterElements as $name => $index) {
                        $message .= "{" . $name . ".debug} ";
                        $replacers[$name] = $generatedArguments[$index];
                    }
                    foreach ($generatedGroup + $this->fixedElements as $name => $value) {
                        $message .= "{" . $name . ".debug} ";
                        $replacers[$name] = $value;
                    }
                    if ($eligible) {
                        $errorCollection?->add(
                            '=',
                            Stringify::parseMessage($message, $replacers)
                        );
                    }
                }

                return $message;
            },
        ];
    }

    public function withFixedElement(string $name, mixed $value): self
    {
        $this->fixedElements[$name] = $value;

        return $this;
    }

    public function withGeneratedElements(\Closure $generator): self
    {
        $this->generatedElements = $generator;

        return $this;
    }

    public function getErrorMeansFail(): bool
    {
        return true;
    }


}
