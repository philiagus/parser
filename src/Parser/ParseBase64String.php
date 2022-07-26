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
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;
use Philiagus\Parser\Contract;

class ParseBase64String extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var bool */
    private bool $strict = true;

    /** @var string */
    private string $notBase64ExceptionMessage = 'The provided value is not a valid base64 sequence';

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * Defines the exception message to use if the value is not a valid base64 string
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Debug::parseMessage()
     *
     */
    public function setNotBase64ExceptionMessage(string $message): self
    {
        $this->notBase64ExceptionMessage = $message;

        return $this;
    }

    /**
     * @param bool $strict
     *
     * @return $this
     */
    public function setStrict(bool $strict = true): self
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function execute(ResultBuilder $builder): \Philiagus\Parser\Contract\Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        $result = base64_decode($value, $this->strict);

        if ($result === false) {
            $builder->logErrorUsingDebug(
                $this->notBase64ExceptionMessage
            );

            return $builder->createResultUnchanged();
        }

        return $builder->createResult($result);
    }

    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type string';
    }

    protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'parse as base64 string';
    }
}
