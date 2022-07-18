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

class Message implements Argument
{

    private array $eligible = [];
    private array $parameterElements = [];

    public function __construct()
    {
    }

    public function withParameterElement(string $name, int $parameter): self
    {
        $this->parameterElements[$parameter] = $name;

        return $this;
    }

    public function expectedWhen(\Closure $when): self
    {
        $this->eligible[] = $when;

        return $this;
    }

    public function generate(mixed $subjectValue): Generator
    {
        yield 'without replacers' => [
            true,
            function (array $generatedArguments, array $successStack, ErrorCollection $errorCollection) use ($subjectValue) {
                $eligible = array_reduce(
                    $this->eligible,
                    fn(bool $carry, \Closure $eligible) => $carry && $eligible($subjectValue, $generatedArguments, $successStack),
                    true
                );
                if($eligible) {
                    $errorCollection->add('MESSAGE WITHOUT REPLACERS');
                }
                return 'MESSAGE WITHOUT REPLACERS';
            },
        ];

        if(!$this->parameterElements) return;

        yield 'with replacers' => [
            true,
            function (array $generatedArguments, array $successStack, ErrorCollection $errorCollection) use ($subjectValue) {
                $message = '{subject.debug} ';
                $replacers = ['subject' => $subjectValue,];
                foreach($this->parameterElements as $index => $parameterElement) {
                    $message .= "\{$parameterElement.debug} ";
                    $replacers[$parameterElement] = $generatedArguments[$index];
                }
                $eligible = array_reduce(
                    $this->eligible,
                    fn(bool $carry, \Closure $eligible) => $carry && $eligible($subjectValue, $generatedArguments, $successStack),
                    true
                );
                if($eligible) {
                    $errorCollection->add(
                        Debug::parseMessage($message, $replacers)
                    );
                }
                return $message;
            },
        ];
    }


}
