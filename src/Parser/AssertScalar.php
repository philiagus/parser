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
use Philiagus\Parser\Base\TypeExceptionMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;


class AssertScalar implements Parser
{
    use Chainable, OverwritableParserDescription, TypeExceptionMessage;

    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function new(): self
    {
        return new self();
    }

    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        if (!is_scalar($subject->getValue())) {
            $this->logTypeError($builder);
        }

        return $builder->createResultUnchanged();
    }

    protected function getDefaultTypeExceptionMessage(): string
    {
        return 'Provided value is not scalar';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'asset scalar';
    }
}
