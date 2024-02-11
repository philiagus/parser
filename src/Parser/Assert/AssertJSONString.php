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

namespace Philiagus\Parser\Parser\Assert;

use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Parser\Parse\ParseJSONString;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Util\Debug;

/**
 * Parser used to assert that a provided value is a string containing a valid JSON.
 * If you need to also extract information from the JSON string please use the ParseJSONString Parser instead.
 *
 * @see ParseJSONString
 */
class AssertJSONString extends Parser
{

    use OverwritableTypeErrorMessage;

    private string $invalidJSONMessage = 'Provided string is not a valid JSON: {message}';

    private function __construct() {}

    public static function new(): static
    {
        return new static();
    }


    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (!is_string($value)) {
            $this->logTypeError($builder);
        } else if (!json_validate($value)) {
            $builder->logErrorUsingDebug(
                $this->invalidJSONMessage,
                [
                    'message' => json_last_error_msg(),
                    'code' => json_last_error()
                ]
            );
        }

        return $builder->createResultUnchanged();
    }

    /**
     * Defines the error message if the provided value is a string but not a valid JSON
     *
     * The message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - message: The string returned from json_last_error_msg
     * - code: The error code returned from json_last_error
     *
     * @param string $message
     * @return $this
     * @see Debug::stringify()
     * @see json_last_error()
     * @see json_last_error_msg()
     */
    public function setInvalidJSONErrorMessage(string $message): static
    {
        $this->invalidJSONMessage = $message;

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not a string and thus not a valid JSON';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert JSON';
    }
}
