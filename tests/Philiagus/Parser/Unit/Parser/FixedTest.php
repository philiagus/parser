<?php
declare(strict_types=1);

namespace Philiagus\Test\Parser\Unit\Parser;

use Exception;
use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Exception\ParserConfigurationException;
use Philiagus\Parser\Parser\Fixed;
use Philiagus\Test\Parser\Provider\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class FixedTest extends TestCase
{

    public function testThatItExtendsBaseParser(): void
    {
        self::assertTrue((new Fixed()) instanceof Parser);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function provideAllTypes(): array
    {
        return DataProvider::provide(DataProvider::TYPE_ALL);
    }

    /**
     * @param $value
     * @dataProvider provideAllTypes
     */
    public function testThatItIgnoresAnyInputAndReturnsTheDefinedValue($value): void
    {
        $instance = new stdClass();
        self::assertSame($instance, (new Fixed())->withValue($instance)->parse($value));
    }

    /**
     * @param $value
     * @dataProvider provideAllTypes
     */
    public function testThatItAcceptsAnyValueAsFixed($value): void
    {
        $instance = new stdClass();
        $result = (new Fixed())->withValue($value)->parse($instance);
        if(is_float($value) && is_nan($value)) {
            self::assertNan($result);
        } else {
            self::assertSame($value, $result);
        }
    }

    public function testThatItThrowsAnExceptionIfNoValueIsDefined(): void
    {
        self::expectException(ParserConfigurationException::class);
        (new Fixed())->parse(null);
    }

}