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


namespace Philiagus\Parser\Contract;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;

interface Error
{

    public function getMessage(): string;

    public function getSourceThrowable(): ?\Throwable;

    /**
     * @return never
     * @throws ParsingException
     */
    public function throw(): never;

    /**
     * @return Subject
     */
    public function getSubject(): Subject;

}
