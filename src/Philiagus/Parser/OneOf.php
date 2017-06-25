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

namespace Philiagus\Parser;

use Philiagus\Parser\Base\Parser;

class OneOf extends Parser implements
    Type\ArrayType,
    Type\BooleanType,
    Type\FloatType,
    Type\IntegerType,
    Type\ObjectType,
    Type\ResourceType,
    Type\StringType
{

    /**
     * @var Contract\Parser
     */
    private $options = [];

    /**
     * @param Contract\Parser $parser
     *
     * @return OneOf
     */
    public function withOption(Contract\Parser $parser): self
    {
        $this->options[] = $parser;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function convert($value, string $path)
    {
        if (empty($this->options)) {
            throw new Exception\ParserConfigurationException('OneOf parser was not provided with any options');
        }

        /** @var Contract\Parser $option */
        foreach ($this->options as $option) {
            try {
                return $option->parse($value, $path);
            } catch (\Throwable $throwable) {
                // ignore exceptions to run through every option availble
            }
        }

        throw new Exception\ParsingException(
            'Provided value does not match any of the expected formats',
            $path
        );
    }
}