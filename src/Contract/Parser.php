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

namespace Philiagus\Parser\Contract;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Exception\RuntimeParserConfigurationException;
use Philiagus\Parser\Result;

interface Parser
{
    /**
     * Triggers this parser to work on the subject provided
     *
     * @param Subject $subject
     *
     * @throws RuntimeParserConfigurationException
     * @throws ParsingException
     */
    public function parse(Subject $subject): Result;
}
