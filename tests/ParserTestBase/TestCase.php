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

namespace Philiagus\Parser\Test\ParserTestBase;

use Philiagus\Parser\Contract;
use Philiagus\Parser\Exception\ParserConfigurationException;

class TestCase
{

    public static int $totalRun = 0;

    public function __construct(
        private readonly bool             $success,
        private readonly bool             $throw,
        private readonly Contract\Subject $subject,
        private readonly \Closure         $parserBuilder,
        private readonly \Closure         $resultValidator,
        private readonly ErrorCollection  $errorCollection,
        private readonly array            $usedArguments
    )
    {

    }

    public function run(): array
    {
        $errors = $this->runInternally();

        return [
            'errors' => $errors,
            'hasError' => !empty($errors),
        ];
    }

    /**
     * @return array{hasErrors: bool, errors: array}
     */
    private function runInternally(): array
    {
        self::$totalRun++;
        $errors = [];

        try {
            $parser = ($this->parserBuilder)();
            $result = $parser->parse($this->subject);
        } catch (\Throwable $e) {
            if (
                $this->errorCollection->isConfigExceptionExpected()
                && $e instanceof ParserConfigurationException
            ) {
                return [];
            }

            $errors = $this->errorCollection->assertException($e);
            if (!$this->success && $this->throw) {
                return $errors;
            }

            return [...$errors, 'Unexpected Exception ' . get_class($e) . ': ' . $e->getMessage()];
        }
        if (!$this->success && $this->throw) {
            return ['No exception thrown, but expected'];
        }
        if ($this->success !== $result->isSuccess()) {
            return ['Success mismatch, should be ' . ($this->subject ? '' : 'NO ') . ' success'];
        }

        return [
            ...$errors,
            ...($this->resultValidator)($this->subject, $result, $this->usedArguments),
            ...$this->errorCollection->assertResult($result),
        ];
    }

}
