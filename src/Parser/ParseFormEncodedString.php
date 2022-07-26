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

namespace Philiagus\Parser\Parser;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\Chainable;
use Philiagus\Parser\Base\OverwritableParserDescription;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;

/**
 * Parses the provided string treating it as form encoded data
 * The result of the parser is the parsed data
 *
 * @see parse_str()
 */
class ParseFormEncodedString extends Base\Parser
{
    use Chainable, OverwritableParserDescription, OverwritableTypeErrorMessage;

    private function __construct()
    {
    }

    /**
     * Returns a new instance of this parser
     *
     * @return static
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        parse_str($value, $result);

        return $builder->createResult($result);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type string';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'parse form encoded';
    }
}
