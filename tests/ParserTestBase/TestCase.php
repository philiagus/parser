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

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Contract\Parser;
use Philiagus\Parser\Exception\ParsingException;

class TestCase
{

    public function __construct(
        private bool            $success,
        private bool            $throw,
        private Subject         $subject,
        private \Closure        $parserBuilder,
        private \Closure        $resultValidator,
        private ErrorCollection $errorCollection
    )
    {

    }

    public function run(): array
    {
        $errors = [];
        /** @var Parser $parser */
        $parser = ($this->parserBuilder)();
        try {
            $result = $parser->parse($this->subject);
        } catch (\Throwable $e) {
            $errors = [...$errors, ...$this->errorCollection->assertException($e)];
            if (!$this->success && $this->throw) {
                return $errors;
            }
            return [...$errors, 'Unexpected Exception ' . get_class($e) . ': ' . $e->getMessage()];
        }
        if (!$this->success && $this->throw) {
            return ['No exception thrown, but expected'];
        }
        if($this->success !== $result->isSuccess()) {
            return ['Success mismatch'];
        }

        $errors = [...$errors, ...($this->resultValidator)($this->subject, $result)];
        $errors = [...$errors, ...$this->errorCollection->assertResult($result)];

        return $errors;
    }

}
