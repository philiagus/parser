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

namespace Philiagus\Parser\Exception;

use Philiagus\Parser\Base\Path;

class OneOfParsingException extends MultipleParsingException
{

    private array $sameOptions;

    private array $equalsOptions;

    /**
     * MultipleParsingException constructor.
     *
     * @param $value
     * @param string $message
     * @param Path $path
     * @param ParsingException[] $parsingExceptions
     * @param array $sameOptions
     * @param array $equalsOptions
     */
    public function __construct($value, string $message, Path $path, array $parsingExceptions,
                                array $sameOptions, array $equalsOptions
    )
    {
        parent::__construct($value, $message, $path, $parsingExceptions);
        $this->sameOptions = $sameOptions;
        $this->equalsOptions = $equalsOptions;
    }

    /**
     * @return mixed[]
     */
    public function getSameOptions(): array
    {
        return $this->sameOptions;
    }

    /**
     * @return mixed[]
     */
    public function getEqualsOptions(): array
    {
        return $this->equalsOptions;
    }

}
