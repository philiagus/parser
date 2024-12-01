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

namespace Philiagus\Parser\Test\Mock;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Error;

readonly class ErrorMock extends Error
{

    public function __construct(
        Subject     $subject = new SubjectMock(),
        string      $message = 'error message',
        ?\Throwable $sourceThrowable = null,
        array       $sourceErrors = []
    )
    {
        parent::__construct($subject, $message, $sourceThrowable, $sourceErrors);
    }

}
