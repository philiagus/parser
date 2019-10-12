# !!! NOT READY FOR PRODUCTION USE !!!

# philiagus/validator
PHP converter classes for asserting, parsing and converting of inputs.

## Why?
Making sure your inputs are what they should be is one of the core principles of not falling into a hackers trap.

Obviously there are more, but we must tackle them one step at a time.

The basic idea of the converters are, that the developer defines a structure through code and later a set of data is throw against this validation and conversion structure.

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
    ->isMultipleOf(10)
    ->parse($integer);
```

The real fun begins, when you start stacking the methods:

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
