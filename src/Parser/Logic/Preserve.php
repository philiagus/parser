<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Philiagus\Parser\Parser\Logic;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Result;
use Philiagus\Parser\Subject\Utility\Forwarded;


/**
 * Preserves a value around another parser, shielding it from alteration
 *
 * @package Parser\Logic
 */
class Preserve extends Base\Parser
{

    private Parser $around;

    protected function __construct(Parser $around)
    {
        $this->around = $around;
    }

    /**
     * Returns an instance of this parser. The provided parser is executed and - on success -
     * the original value provided to the Preserve parser is returned instead of the
     * potentially altered value of the provided parser
     *
     * @param Parser $parser
     *
     * @return static
     */
    public static function around(Parser $parser): static
    {
        return new static($parser);
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Result
    {
        $builder->unwrapResult(
            $this->around->parse(
                new Forwarded($builder->getSubject(), 'preserved around')
            )
        );

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'preserved';
    }
}
