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

namespace Philiagus\Parser\Util;

class Debug
{

    /**
     * Defines the maximum string length
     */
    private const MAX_STRING_LENGTH = 32;

    /**
     * List of control characters that can be replaced with corresponding unicode characters
     */
    private const CONTROL_REPLACERS = [
        "\x00" => '␀', "\x01" => '␁', "\x02" => '␂', "\x03" => '␃', "\x04" => '␄',
        "\x05" => '␅', "\x06" => '␆', "\x07" => '␇', "\x08" => '␈', "\x09" => '␉',
        "\x0A" => '␊', "\x0B" => '␋', "\x0C" => '␌', "\x0D" => '␍', "\x0E" => '␎',
        "\x0F" => '␏', "\x10" => '␐', "\x11" => '␑', "\x12" => '␒', "\x13" => '␓',
        "\x14" => '␔', "\x15" => '␕', "\x16" => '␖', "\x17" => '␗', "\x18" => '␘',
        "\x19" => '␙', "\x1A" => '␚', "\x1B" => '␛', "\x1C" => '␜', "\x1D" => '␝',
        "\x1E" => '␞', "\x1F" => '␟', "\x7F" => '␡',
    ];

    /**
     * Uses $message as a string and $replacers as an array of elements to be replaced into it
     * The replacement elements look like this "{arrayKey}", performing a raw replacement, or
     * "{arrayKey.infoType}", transforming the value before replacing.
     *
     * infoType can be one of the following:
     * gettype: The result of a call to gettype on the replacers element
     * type: the same as gettype for anything but objects. For objects its "object<className>"
     * debug: a string representation of the value, tying to show as much of the content as possible, see Debug::stringify
     * export: the result of var_export of the value
     * raw: the raw value form the replacers
     *
     * Only valid replacers are replaced. If the key or the infoType is not known that replacer won't be replaced.
     *
     * @param string $message
     * @param array $replacers
     *
     * @return string
     */
    public static function parseMessage(
        string $message,
        array  $replacers
    ): string
    {
        return preg_replace_callback(
            '~{(?<key>[a-z]++)(?:\.(?<info>[a-z]+))?}~i',
            function ($matches) use ($replacers): string {
                $key = $matches['key'];
                if (!array_key_exists($key, $replacers)) {
                    return $matches[0];
                }
                $info = $matches['info'] ?? 'raw';
                $value = $replacers[$key];
                switch ($info) {
                    case 'gettype':
                        return gettype($value);
                    case 'type':
                        return self::getType($value);
                    case 'debug':
                        return self::stringify($value);
                    case 'export':
                        return var_export($value, true);
                    case 'raw':
                        if (is_array($value)) {
                            return 'Array';
                        }
                        if (is_object($value)) {
                            return 'Object';
                        }
                        if (is_resource($value)) {
                            return self::stringify($value);
                        }

                        return (string) $value;
                    default:
                        return $matches[0];
                }
            },
            $message
        );
    }

    /**
     * Returns a string representation of the type of the provided variable
     * NAN, INF and -INF are represented as corresponding strings
     * objects are represented as "object<className>"
     * all other values will simply return whatever gettype returns
     *
     * @param $value
     *
     * @return string
     */
    public static function getType($value): string
    {
        if (is_object($value)) {
            return 'object<' . get_class($value) . '>';
        }

        if (is_float($value)) {
            if (is_nan($value)) {
                return 'NAN';
            }

            if (is_infinite($value)) {
                return $value > 0 ? 'INF' : '-INF';
            }

            return 'float';
        }

        return gettype($value);
    }

    /**
     * Converts a value to a string representation of that value
     *
     * @param $value
     *
     * @return string
     */
    public static function stringify($value): string
    {
        $type = gettype($value);
        switch (gettype($value)) {
            case 'integer':
                return "$type $value";
            case 'double':
                if (is_nan($value)) {
                    return "NAN";
                }
                if (is_infinite($value)) {
                    if ($value > 0) {
                        return "INF";
                    }

                    return "-INF";
                }

                return "float $value";
            case 'boolean':
                if ($value) {
                    return "$type TRUE";
                }

                return "$type FALSE";
            case 'string':
                $encoding = mb_detect_encoding($value, 'ASCII, UTF8', true);
                $length = strlen($value);
                if ($encoding) {
                    // replace known control characters
                    $value = strtr($value, self::CONTROL_REPLACERS);
                } else {
                    $encoding = 'binary';
                }

                if ($encoding === 'binary') {
                    $print = '';
                } else {
                    if (mb_strlen($value, 'UTF8') > self::MAX_STRING_LENGTH) {
                        $print = '"' . mb_substr($value, 0, self::MAX_STRING_LENGTH - 1) . "\u{2026}\"";
                    } else {
                        $print = '"' . $value . '"';
                    }
                }

                return "string<$encoding>({$length})$print";
            case 'array':
                if (empty($value)) {
                    return 'array(0)';
                }
                $keyType = null;
                $valueType = null;
                foreach ($value as $arrayKey => $arrayValue) {
                    if ($keyType !== 'mixed') {
                        $currentKeyType = self::getType($arrayKey);
                        if ($keyType === null) {
                            $keyType = $currentKeyType;
                        } elseif ($keyType !== $currentKeyType) {
                            $keyType = 'mixed';
                        }
                    }

                    if ($valueType !== 'mixed') {
                        $currentValueType = self::getType($arrayValue);
                        if ($valueType === null) {
                            $valueType = $currentValueType;
                        } elseif ($valueType !== $currentValueType) {
                            $valueType = 'mixed';
                        }
                    }
                    if ($keyType === 'mixed' && $valueType === 'mixed') break;
                }

                return "array<$keyType,$valueType>(" . count($value) . ")";
            case "object":
            case "resource":
            case "resource (closed)":
            case "unknown type":
            case "NULL":
            default:
                return self::getType($value);
        }
    }

}
