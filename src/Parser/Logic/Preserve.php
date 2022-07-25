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

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\Utility\Forwarded;


/**
 * Preserves a value around another parser, shielding it from alteration
 */
class Preserve extends Base\Parser
{

    /** @var Parser */
    private Parser $around;

    /**
     * Preserve constructor.
     *
     * @param Parser $around
     */
    private function __construct(Parser $around)
    {
        $this->around = $around;
    }

    /**
     * @param Parser $parser
     *
     * @return static
     */
    public static function around(Parser $parser): self
    {
        return new self($parser);
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Result
    {
        $builder->incorporateResult(
            $this->around->parse(
                new Forwarded($builder->getSubject(), 'preserved around')
            )
        );

        return $builder->createResultUnchanged();
    }

    protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'preserved';
    }
}
