# philiagus/validator
PHP converter classes for sanitization, parsing and conversion of inputs.

## Why?
Making sure your inputs are what they should be is one of the core principles of not falling into a hackers trap.

Obviously there are more, but we must tackle them one step at a time.

The basic idea of the converters are, that the developer defines a structure through code and later a set of data is throw against this validiation and conversion structure.

A simple example:

```php
<?php
(new IntegerPrimitive())
    ->withMinimum(0)
    ->validate($integer);
```

