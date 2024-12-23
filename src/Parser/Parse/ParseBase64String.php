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

namespace Philiagus\Parser\Parser\Parse;

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Result;
use Philiagus\Parser\Util\Stringify;

/**
 * Parser used to base64 decode a string
 *
 * This parser uses strict decoding by default, but can be set to be non-strict using
 * the setStrict method
 *
 * @see base64_decode()
 * @package Parser\Parse
 * @target-type string
 */
class ParseBase64String extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    private bool $strict = true;
    private string $notBase64ExceptionMessage = 'The provided value is not a valid base64 sequence';

    protected function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Defines the exception message to use if the value is not a valid base64 string
     *
     * The message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     *
     * @param string $message
     *
     * @return $this
     * @see Stringify::parseMessage()
     *
     */
    public function setNotBase64ErrorMessage(string $message): static
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

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);

            return $builder->createResultUnchanged();
        }

        $result = base64_decode($value, $this->strict);

        if ($result === false) {
            $builder->logErrorStringify(
                $this->notBase64ExceptionMessage
            );

            return $builder->createResultUnchanged();
        }

        return $builder->createResult($result);
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type string';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Subject $subject): string
    {
        return 'parse as base64 string';
    }
}
