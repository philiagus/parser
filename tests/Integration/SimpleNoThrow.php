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

namespace Philiagus\Parser\Test\Integration;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\Assert\AssertInteger;
use Philiagus\Parser\Parser\Assert\AssertStringMultibyte;
use Philiagus\Parser\Parser\Parse\ParseArray;
use Philiagus\Parser\Test\TestBase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class SimpleNoThrow extends TestBase
{

    public function test(): void
    {
        $parser = ParseArray::new()
            ->giveValue(
                'id',
                AssertInteger::new()
                    ->thenAssignTo($id)
            )
            ->giveValue(
                'name',
                AssertStringMultibyte::new()
                    ->setEncoding('UTF-8')
                    ->giveLength(
                        AssertInteger::new()
                            ->assertMinimum(1)
                            ->assertMaximum(100)
                    )
                    ->thenAssignTo($name)
            );

        $result = $parser
            ->parse(
                Subject::default(
                    [
                        'id' => 'Hallo Welt',
                        'name' => '',
                    ],
                    'start',
                    false
                )
            );

        foreach ($result->getErrors() as $error) {
            echo $error->getSubject()->getPathAsString(true) . ': ' . $error->getMessage(), PHP_EOL;
        }

        $errors = $result->getErrors();
        self::assertCount(2, $errors);
        [$e1, $e2] = $errors;
        self::assertSame(
            'start[id]: Provided value is not of type integer',
            $e1->getSubject()->getPathAsString() . ': ' . $e1->getMessage()
        );
        self::assertSame(
            'start[name] length in UTF-8: Provided value integer 0 is lower than the defined minimum of 1',
            $e2->getSubject()->getPathAsString() . ': ' . $e2->getMessage()
        );
    }

}
