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


namespace Philiagus\Parser\Test\Mock;

use Philiagus\DataProvider\DataProvider;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Error;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Test\ParserTestBase\ErrorCollection;
use Philiagus\Parser\Util\Debug;

class ParserMock implements Parser
{

    private array $expectedCalls = [];

    public function error(?ErrorCollection $errorCollection = null): self
    {
        $this->expect(
            fn() => true,
            fn() => true,
            static function (Subject $subject) use ($errorCollection) {
                $message = uniqid(microtime());
                $error = new Error($subject, $message);
                $errorCollection?->add($error->getMessage());
                if ($subject->throwOnError()) {
                    throw new ParsingException($error);
                }

                return new Result($subject, null, [$error]);
            },
            INF
        );

        return $this;
    }

    public function expect(
        mixed           $value,
        \Closure|string $pathType,
        \Closure        $result = null,
        float|int       $count = 1
    ): self
    {
        $this->expectedCalls[] = [
            'value' => $value instanceof \Closure ? $value : static function (Subject $subject) use ($value): void {
                if (!DataProvider::isSame($subject->getValue(), $value)) {
                    throw new \RuntimeException("Value does not match " . Debug::stringify($subject->getValue()) . " <-> " . Debug::stringify($value));
                }
            },
            'path' => $pathType instanceof \Closure ? $pathType : static function (Subject $subject) use ($pathType) {
                if (!$subject instanceof $pathType) {
                    throw new \RuntimeException("Path type " . $subject::class . " does not match expected $pathType");
                }
            },
            'result' => $result ?? static fn(Subject $subject) => new Result($subject, $subject->getValue(), []),
            'count' => $count,
        ];

        return $this;
    }

    public function parse(Subject $subject): Result
    {
        $next = array_shift($this->expectedCalls);
        if (!$next) {
            throw new \LogicException("No further call to parser expected");
        }

        $next['value']($subject);
        $next['path']($subject);
        $next['count']--;
        if ($next['count'] > 0) {
            array_unshift($this->expectedCalls, $next);
        }

        return $next['result']($subject);
    }

    public function acceptAnything(null|int|float $times = null): self
    {
        return $this->expect(
            fn() => true,
            fn() => true,
            static fn(Subject $subject) => new Result($subject, $subject->getValue(), []),
            $times ?? INF,
        );
    }
}
