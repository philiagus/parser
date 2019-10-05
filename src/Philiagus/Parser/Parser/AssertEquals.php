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
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Exception\ParsingException;
use Philiagus\Parser\Type\AcceptsMixed;

class AssertEquals
    extends Parser
    implements AcceptsMixed
{

    /**
     * @var bool
     */
    private $valueSet = false;

    /**
     * @var mixed
     */
    private $targetValue;

    public function withValue($equalsValue): self
    {
        $this->valueSet = true;
        $this->targetValue = $equalsValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function execute($value, Path $path)
    {
        if(!$this->valueSet) {
            throw new ParserConfigurationException('Called AssertEquals parse without a value set');
        }

        if($value != $this->targetValue) {
            throw new ParsingException($value, 'The value is not equal to the expected value', $path);
        }

        return $value;
    }
}