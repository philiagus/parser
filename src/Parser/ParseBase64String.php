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
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

/**
 * Parser used to base64 decode a string
 * This parser uses strict decoding by default, but can be set to be non-strict using
 * the setStrict method
 *
 * @see base64_decode()
 */
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
    public function setNotBase64ExceptionMessage(string $message): static
    {
        $this->notBase64ExceptionMessage = $message;

        return $this;
    }

    /**
     * Sets the base64 decode to be no-strict
     *
     * @param bool $strict
     *
     * @return $this
     * @see base64_decode()
     */
    public function setStrict(bool $strict = true): static
    {
        $this->strict = $strict;

        return $this;
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

        $result = base64_decode($value, $this->strict);

        if ($result === false) {
            $builder->logErrorUsingDebug(
                $this->notBase64ExceptionMessage
            );

            return $builder->createResultUnchanged();
        }

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
        return 'parse as base64 string';
    }
}
