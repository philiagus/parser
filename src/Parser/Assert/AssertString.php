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

use Philiagus\Parser\Base;
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Subject\MetaInformation;

/**
 * Parser used to assert that a value is a string. This parser treats the value as a normal PHP string,
 * ignoring the encoding of the string and not trying to identify it.
 * If you need to respect the encoding of the string (such as when dealing with multibyte character sequences
 * as used in for example UTF-8), please use the AssertStringMultibyte parser
 *
 * @package Parser\Assert
 * @see AssertStringMultibyte
 * @target-type string
 */
class AssertString extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var \SplDoublyLinkedList<\Closure> */
    private \SplDoublyLinkedList $assertionList;

    protected function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * Executes strlen on the string and hands the result over to the parser
     *
     * @param ParserContract $integerParser
     *
     * @return $this
     */
    public function giveLength(ParserContract $integerParser): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, string $value) use ($integerParser): void {
            $builder->unwrapResult(
                $integerParser->parse(
                    new MetaInformation($builder->getSubject(), 'length', strlen($value))
                )
            );
        };

        return $this;
    }

    /**
     * Performs substr on the string and executes the parser on that part of the string
     *
     * @param int $start
     * @param int|null $length
     * @param ParserContract $stringParser
     *
     * @return $this
     */
    public function giveSubstring(
        int            $start,
        ?int           $length,
        ParserContract $stringParser
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, string $value) use ($start, $length, $stringParser): void {
            if ($value === '') {
                $part = '';
            } else {
                $part = substr($value, $start, $length);
            }
            $builder->unwrapResult(
                $stringParser->parse(
                    new MetaInformation($builder->getSubject(), "excerpt from $start to " . ($length ?? 'end'), $part)
                )
            );
        };

        return $this;
    }

    /**
     * Checks that the string starts with the provided string and fails if it doesn't
     *
     * The error message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The expected string
     *
     * @param string $string
     * @param string $errorMessage
     *
     * @return $this
     */
    public function assertStartsWith(
        string $string,
        string $errorMessage = 'The string does not start with {expected.debug}'
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, string $value) use ($string, $errorMessage): void {
            if (!str_starts_with($value, $string)) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['expected' => $string]
                );
            }
        };

        return $this;
    }

    /**
     * Checks that the string ends with the provided string and fails if it doesn't
     *
     * The error message is processed using Stringify::parseMessage and receives the following elements:
     * - value: The value currently being parsed
     * - expected: The expected string
     *
     * @param string $string
     * @param string $errorMessage
     *
     * @return $this
     */
    public function assertEndsWith(
        string $string,
        string $errorMessage = 'The string does not end with {expected.debug}'
    ): static
    {
        $this->assertionList[] = static function (ResultBuilder $builder, string $value) use ($string, $errorMessage): void {
            if (!str_ends_with($value, $string)) {
                $builder->logErrorStringify(
                    $errorMessage,
                    ['expected' => $string]
                );
            }
        };

        return $this;
    }

    /** @inheritDoc */
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        $value = $builder->getValue();
        if (is_string($value)) {
            foreach ($this->assertionList as $assertion) {
                $assertion($builder, $value);
            }
        } else {
            $this->logTypeError($builder);
        }

        return $builder->createResultUnchanged();
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type string';
    }

    /** @inheritDoc */
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'assert binary sequence';
    }
}
