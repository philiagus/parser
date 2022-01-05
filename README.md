# philiagus/parser

PHP classes to assert, convert and parse data.

# Is it tested?

Tested on the following PHP Version:

- PHP7.4
- PHP8.0

100% test covered. Test coverage generated on PHP7.2

## Why do I need it?

Making sure your inputs are what they should be is one of the core principles of secure coding.

Obviously there are more, but we must tackle them one step at a time.

The basic idea of the parsers is, that the developer defines a structure through code and later throws a set of data
against it. The parsers make sure, that the data is following the rules of defined in the structure.

## How does it work?

A simple example:

```php
<?php
use Philiagus\Parser\Parser\AssertInteger;use Philiagus\Parser\Parser\Extraction\Assign;

$integer = 100;

$parsingResult = AssertInteger::new()
    ->assertMinimum(0)
    ->assertMaximum(100)
    ->parse($integer);

// or, also possible:
AssertInteger::new()
    ->assertMinimum(0)
    ->assertMaximum(10)
    ->then(Assign::to($target))
    ->parse($integer);
```

The real fun begins, when you start stacking parsers into one another:

```php
<?php
use Philiagus\Parser\Parser\AssertFloat;use Philiagus\Parser\Parser\AssertInteger;use Philiagus\Parser\Parser\Logic\OneOf;use Philiagus\Parser\Parser\ParseArray;

$input = [
    1, 1.0, 2, 4, 4.20
];

$integers = [];
$floats = [];

ParseArray::new()
    ->assertSequentialKeys()
    ->giveEachValue(
        OneOf::new()
            ->parser(
                AssertInteger::new()
                    ->assertMinimum(0)
                    ->thenAppendTo($integers)
                    ,
                AssertFloat::new()
                    ->assertMinimum(0.0)
                    ->thenAppendTo($floats)
            )
    )
->parse($input);

// $integers will contain [1, 2, 4]
// $floats will contain [1.0, 4.20]

```

## What if something is missing?

Fear not! All parsers implement `Philiagus\Parser\Contract\Parser`, so you can easily write your own to fit your
specific need.
