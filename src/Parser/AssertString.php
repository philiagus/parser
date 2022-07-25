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
use Philiagus\Parser\Base\OverwritableTypeErrorMessage;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Contract\Parser as ParserContract;
use Philiagus\Parser\Result;
use Philiagus\Parser\ResultBuilder;
use Philiagus\Parser\Subject\MetaInformation;

class AssertString extends Base\Parser
{
    use OverwritableTypeErrorMessage;

    /** @var \SplDoublyLinkedList<\Closure> */
    private \SplDoublyLinkedList $assertionList;

    private function __construct()
    {
        $this->assertionList = new \SplDoublyLinkedList();
    }

    /**
     * @return self
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Executes strlen on the string and hands the result over to the parser
     *
     * @param ParserContract $integerParser
     *
     * @return $this
     */
    public function giveLength(ParserContract $integerParser): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder, string $value) use ($integerParser): void {
            $builder->incorporateResult(
                $integerParser->parse(
                    new MetaInformation($builder->getSubject(), 'length', strlen($value))
                )
            );
        };

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(ResultBuilder $builder): Result
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
    ): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder, string $value) use ($start, $length, $stringParser): void {
            if ($value === '') {
                $part = '';
            } else {
                $part = substr($value, $start, $length);
            }
            $builder->incorporateResult(
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
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - expected: The expected string
     *
     * @param string $string
     * @param string $message
     *
     * @return $this
     */
    public function assertStartsWith(
        string $string,
        string $message = 'The string does not start with {expected.debug}'
    ): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder, string $value) use ($string, $message): void {
            if (!str_starts_with($value, $string)) {
                $builder->logErrorUsingDebug(
                    $message,
                    ['expected' => $string]
                );
            }
        };

        return $this;
    }

    /**
     * Checks that the string ends with the provided string and fails if it doesn't
     *
     * The exception message is processed using Debug::parseMessage and receives the following elements:
     * - subject: The value currently being parsed
     * - expected: The expected string
     *
     * @param string $string
     * @param string $message
     *
     * @return $this
     */
    public function assertEndsWith(
        string $string,
        string $message = 'The string does not end with {expected.debug}'
    ): self
    {
        $this->assertionList[] = static function (ResultBuilder $builder, string $value) use ($string, $message): void {
            if (!str_ends_with($value, $string)) {
                $builder->logErrorUsingDebug(
                    $message,
                    ['expected' => $string]
                );
            }
        };

        return $this;
    }

    protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of type string';
    }

    protected function getDefaultChainDescription(Subject $subject): string
    {
        return 'assert binary sequence';
    }
}
