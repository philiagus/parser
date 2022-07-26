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
use Philiagus\Parser\Util\Debug;
use Philiagus\Parser\Contract;

class AssertNan extends Base\Parser
{

    /** @var string */
    private string $exceptionMessage;

    private function __construct(string $message)
    {
        $this->exceptionMessage = $message;
    }

    /**
     * Sets the exception message to be thrown when the provided value is not NAN
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $notNanExceptionMessage
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public static function new(string $notNanExceptionMessage = 'Provided value is not NAN'): self
    {
        return new self($notNanExceptionMessage);
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): \Philiagus\Parser\Contract\Result
    {
        $value = $builder->getValue();
        if (!is_float($value) || !is_nan($value)) {
            $builder->logErrorUsingDebug($this->exceptionMessage);
        }

        return $builder->createResultUnchanged();
    }

    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert NaN';
    }
}
