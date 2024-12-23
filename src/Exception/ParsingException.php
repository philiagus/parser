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

namespace Philiagus\Parser\Exception;

use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Error;

/**
 * This exception is supposed to be thrown, when the value as provided by the input does not conform with the parser
 * e.g. when the value could not be converted to the target type or when the provided type is not of the correct type
 *
 * @package Error
 */
class ParsingException extends \Exception
{

    /**
     * @param Error $error
     */
    public function __construct(private readonly Error $error)
    {
        parent::__construct($this->error->getMessage(), 0, $this->error->getSourceThrowable());
    }

    /**
     * Returns the path string of the subject the error in this parsing exception was targeting
     *
     * @param bool $includeUtility
     *
     * @return string
     * @see Subject::getPathAsString()
     */
    public function getPathAsString(bool $includeUtility = false): string
    {
        return $this->getSubject()->getPathAsString($includeUtility);
    }

    /**
     * Returns the subject that this error was identified on
     *
     * @return Subject
     */
    public function getSubject(): Subject
    {
        return $this->error->getSubject();
    }

    /**
     * Returns the error that lead to this exception
     *
     * @return Error
     */
    public function getError(): Error
    {
        return $this->error;
    }
}
