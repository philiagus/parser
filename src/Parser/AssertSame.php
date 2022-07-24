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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;

class AssertSame extends Base\Parser
{

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

    /**
     * @inheritDoc
     */
    public function execute(ResultBuilder $builder): Result
    {
        if ($builder->getValue() !== $this->value) {
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
