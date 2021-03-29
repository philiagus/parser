# philiagus/parser
PHP classes to assert, convert and parse data.

# Is it tested?
Tested on the following PHP Version:
- PHP7.2
- PHP7.3
- PHP7.4
- PHP8.0

100% test covered. Test coverage generated on PHP7.2

## Why do I need it?
Making sure your inputs are what they should be is one of the core principles of secure coding.

Obviously there are more, but we must tackle them one step at a time.

The basic idea of the parsers is, that the developer defines a structure through code and later throws a set of data against it. The parsers make sure, that the data is following the rules of defined in the structure.

## How does it work?

A simple example:

```php
<?php
use Philiagus\Parser\Parser\AssertInteger;

$integer = 100;

$parsingResult = (new AssertInteger())
    ->withMinimum(0)
    ->withMaximum(100)
    ->parse($integer);

// or, also possible:
(new AssertInteger($parsingResult))
    ->withMinimum(0)
    ->withMultipleOf(10)
    ->parse($integer);
```

The real fun begins, when you start stacking parsers into one another:

```php
<?php
use Philiagus\Parser\Parser\OneOf;
use Philiagus\Parser\Parser\AssertInteger;
use Philiagus\Parser\Parser\AssertFloat;
use Philiagus\Parser\Parser\AssertArray;

$parser = (new AssertArray())
    ->withEachValue(
        (new OneOf())
            ->addOption(
                (new AssertInteger())
                    ->withMinimum(0)
            )
            ->addOption(
                (new AssertFloat())
                    ->withMinimum(0.0)
            )
    );
```

## What if something is missing?

Fear not! All parsers implement `Philiagus\Parser\Contract\Parser`, so you can easily write your own to fit your specific need.