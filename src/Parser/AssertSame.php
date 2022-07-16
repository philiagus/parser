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

use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class AssertSame implements Parser
{
    use Chainable, OverwritableParserDescription;

    private function __construct(
        private readonly mixed $value,
        private readonly mixed $exceptionMessage
    )
    {
    }

    /**
     * @param mixed $value
     * @param string $exceptionMessage
     *
     * @return self
     */
    public static function value(mixed $value, string $exceptionMessage = 'The value is not the same as the expected value'): self
    {
        return new self($value, $exceptionMessage);
    }

    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        if ($subject->getValue() !== $this->value) {
            $builder->logErrorUsingDebug(
                $this->exceptionMessage,
                ['expected' => $this->value]
            );
        }

        return $builder->createResultUnchanged();
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'assert same';
    }
}
