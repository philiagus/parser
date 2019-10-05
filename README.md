***!!! NOT READY FOR PRODUCTION USE !!!***

# philiagus/validator
PHP converter classes for asserting, parsing and converting of inputs.

## Why?
Making sure your inputs are what they should be is one of the core principles of not falling into a hackers trap.

Obviously there are more, but we must tackle them one step at a time.

The basic idea of the converters are, that the developer defines a structure through code and later a set of data is throw against this validiation and conversion structure.

A simple example:

```php
<?php
use Philiagus\Parser\IntegerPrimitive;

$integer = 100;

$parsingResult = (new IntegerPrimitive())
    ->withRange(0, 100)
    ->parse($integer);

// or, also possible:
(new IntegerPrimitive($parsingResult))
    ->withMinimum(0)
    ->withDivisibleBy(10)
    ->parse($integer);
```

The real fun begins, when you start stacking the methods:

```php
<?php
use Philiagus\Parser\OneOf;
use Philiagus\Parser\ArrayPrimitive;
use Philiagus\Parser\IntegerPrimitive;
use Philiagus\Parser\FloatPrimitive;

$parser = (new ArrayPrimitive())
    ->withValue(
        (new OneOf())
            ->withOption(
                (new IntegerPrimitive())
                    ->withMinimum(0)
            )
            ->withOption(
                (new FloatPrimitive())
                    ->withMinimum(0)
            )
    );
```
