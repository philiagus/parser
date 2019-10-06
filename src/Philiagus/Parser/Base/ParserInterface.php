<?php
declare(strict_types=1);

namespace Philiagus\Parser\Base;

use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;

interface ParserInterface
{
    /**
     * @param mixed $value
     * @param Path|null $path
     *
     * @return mixed
     * @throws ParserConfigurationException
     * @throws ParsingException
     */
    public function parse($value, ?Path $path = null);
}