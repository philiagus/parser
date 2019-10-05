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

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Path;
use Philiagus\Parser\Exception;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Type;
use Philiagus\Parser\Type\AcceptsMixed;

class OneOf extends Parser implements
    Type\AcceptsArray,
    Type\AcceptsBoolean,
    Type\AcceptsFloat,
    Type\AcceptsInteger,
    Type\AcceptsObject,
    Type\AcceptsResource,
    Type\AcceptsString
{

    /**
     * @var AcceptsMixed[]
     */
    private $options = [];

    public function withOption(AcceptsMixed $parser): self
    {
        $this->options[] = $parser;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if (empty($this->options)) {
            throw new Exception\ParserConfigurationException('OneOf parser was not provided with any options');
        }

        $exceptions = [];

        /** @var Parser $option */
        foreach ($this->options as $option) {
            try {
                return $option->parse($value, $path);
            } catch (ParsingException $exception) {
                $exceptions[] = $exception;
            }
        }

        throw new Exception\MultipleParsingException(
            $value,
            'Provided value does not match any of the expected formats',
            $path,
            $exceptions
        );
    }
}