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
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Debug;

class AssertNan implements Parser
{
    use Chainable, OverwritableParserDescription;

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

    public function parse(Subject $subject): Result
    {
        $builder = $this->createResultBuilder($subject);
        $value = $subject->getValue();
        if (!is_float($value) || !is_nan($value)) {
            $builder->logErrorUsingDebug($this->exceptionMessage);
        }

        return $builder->createResultUnchanged();
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'assert NaN';
    }
}
