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

namespace Philiagus\Parser\Test;

use Philiagus\Parser\Util\Debug;
use PHPUnit\Framework\Assert;

class Util
{

    public static function assertSame(mixed $a, mixed $b): void
    {
        if ($a instanceof \DateTimeInterface && $b instanceof \DateTimeInterface) {
            Assert::assertInstanceOf($a::class, $b);
            Assert::assertSame($a->format('r'), $b->format('r'));
            return;
        } elseif (gettype($a) === gettype($b)) {
            if (is_float($a) && is_nan($a)) {
                Assert::assertNan($b);

                return;
            }
            if (is_null($a) || is_scalar($a) || is_object($a) || is_resource($a)) {
                Assert::assertSame($a, $b);

                return;
            }
            if (is_array($a)) {
                Assert::assertSame(array_keys($a), array_keys($b), 'Array keys do not match');

                foreach ($a as $key => $value) {
                    self::assertSame($value, $b[$key]);
                }
                return;
            }
        }

        Assert::fail(
            Debug::parseMessage(
                'The two values do not match: {a.debug} <=> {b.debug}',
                [
                    'a' => $a,
                    'b' => $b,
                ]
            )
        );
    }

    public static function isSame(mixed $a, mixed $b): bool
    {
        if ($a instanceof \DateTimeInterface && $b instanceof \DateTimeInterface) {
            if ($a::class !== $b::class) return false;
            if ($a->format('r') !== $b->format('r')) return false;
        } elseif (gettype($a) === gettype($b)) {
            if (is_float($a) && is_nan($a)) {
                return is_nan($b);
            }
            if (is_null($a) || is_scalar($a) || is_object($a) || is_resource($a)) {
                return $a === $b;
            }
            if (is_array($a)) {
                if (array_keys($a) !== array_keys($b)) {
                    return false;
                }
                foreach ($a as $key => $value) {
                    if (!self::isSame($value, $b[$key])) {
                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }
}
