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

namespace Philiagus\Parser\Test;

use Philiagus\Parser\Contract\Subject;
use Philiagus\Parser\Test\Mock\SubjectMock;

abstract class SubjectTestBase extends TestBase
{

    abstract protected function createChained(Subject $parent): Subject;

    public function testMemoryPersistence(): void
    {
        $subject1 = new SubjectMock(null, 'Subject 1', null, true, true);
        $subject2 = $this->createChained($subject1);
        $subject3 = new SubjectMock($subject2, 'Subject 3', null, true, true);
        self::assertSame($subject1->getFullMemory(), $subject2->getFullMemory());

        /** @var Subject $writingObject */
        foreach([$subject1, $subject2, $subject3] as $writingObject) {
            /** @var Subject $readingObject */
            foreach([$subject1, $subject2, $subject3] as $readingObject) {
                self::assertFalse($readingObject->hasMemory($writingObject));
            }
        }

        /** @var Subject $writingObject */
        foreach([$subject1, $subject2, $subject3] as $writingObject) {
            /** @var Subject $readingObject */
            $writingObject->setMemory($writingObject, $writingObject);
            foreach([$subject1, $subject2, $subject3] as $readingObject) {
                self::assertTrue($readingObject->hasMemory($writingObject));
                self::assertSame($writingObject, $readingObject->getMemory($writingObject));
            }
        }
    }
}
