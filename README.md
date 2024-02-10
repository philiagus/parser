# philiagus/parser

PHP classes to assert, convert and parse data.

# Is it tested?

Tested on the following PHP Version:

- PHP8.3

100% test covered. Test coverage generated on PHP8.3

## Why do I need it?

Making sure your inputs are what they should be is one of the core principles of secure coding.

Obviously there are more, but we must tackle them one step at a time.

The basic idea of the parsers is, that the developer defines a structure through code and later throws a set of data
against it. The parsers make sure, that the data is following the rules of defined in the structure.

## How does it work?

A simple example:

```php
<?php
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\Assert\AssertInteger;

$integer = 100;

$parsingResult = AssertInteger::new()
    ->assertMinimum(0)
    ->assertMaximum(100)
    ->parse(Subject::default($integer))
    ->getValue();

// or, also possible:
AssertInteger::new()
    ->assertMinimum(0)
    ->assertMaximum(10)
    ->thenAssignTo($target)
    ->parse(Subject::default($integer))
    ->getValue();
```

The real fun begins, when you start stacking parsers into one another:

```php
<?php
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\Assert\AssertFloat;
use Philiagus\Parser\Parser\Assert\AssertInteger;
use Philiagus\Parser\Parser\Logic\OneOf;
use Philiagus\Parser\Parser\Parse\ParseArray;

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
                    ->thenAppendTo($integers),
                AssertFloat::new()
                    ->assertMinimum(0.0)
                    ->thenAppendTo($floats)
            )
    )
->parse(Subject::default($input));

// $integers will contain [1, 2, 4]
// $floats will contain [1.0, 4.20]

```

Let's say: You have an API which receives requests from a client in JSON format, and you need to find the relevant information.

```php
<?php
use Philiagus\Parser\Base\Subject;
use Philiagus\Parser\Parser\Assert\AssertInteger;
use Philiagus\Parser\Parser\Assert\AssertStdClass;
use Philiagus\Parser\Parser\Assert\AssertStringMultibyte;
use Philiagus\Parser\Parser\Convert\ConvertToDateTime;
use Philiagus\Parser\Parser\Parse\ParseJSONString;

$sourceValue = '{"name":"Frank Herbert","birthday":"1920-10-08"}';

$parser = ParseJSONString::new()
    ->then(
        AssertStdClass::new()
            ->givePropertyValue(
                'name',
                AssertStringMultibyte::UTF8()
                    ->giveLength(
                        AssertInteger::new()
                            ->assertMinimum(1)
                            ->assertMaximum(64)
                    )
                    ->thenAssignTo($name)
            )
            ->givePropertyValue(
                'birthday',
                ConvertToDateTime::fromSourceFormat(
                    '!Y-m-d', new \DateTimeZone('UTC'),
                    'The provided birthday is not a valid date'
                )
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->thenAssignTo($birthday)
            )
    );
    
$result = $parser->parse(Subject::default($sourceValue, 'Input', false));

if ($result->hasErrors()) {
    foreach ($result->getErrors() as $error) {
        echo $error->getPathAsString(), ': ', $error->getMessage(), PHP_EOL;
    }
    exit;
}

$today = new \DateTime();
$delta = $today->diff($birthday);
if ($today < $birthday) {
    echo "$name will be born in ", $delta->y, " years", PHP_EOL;
} else {
    echo "$name was born ", $delta->y, " years ago", PHP_EOL;
}
```

If you execute this code the result will be `Frank herbert was born 101 years ago` (at least on the date of this typing).

Would you change the input to `{"name":123,"birthday":"1920-10-0f"}`, the result would be:
```text
Input.name: Provided value is not of type string
Input.birthday: The provided birthday is not a valid date
```

## What if something is missing?

Fear not! All parsers implement `Philiagus\Parser\Contract\Parser`, so you can easily write your own to fit your specific need. Check out `Philiagus\Parser\Base\Parser` for a base class you can easily extend.

Some hints:
- If your parser validates a type (example: a parser the reads an XML, so non-strings are a no-go), the `Philiagus\Parser\Base\OverwritableTypeErrorMessage`-Trait might help
- The `Philiagus\Parser\Base\Parser` already implements basic things such as chaining using `->then($parser)` among other things, so you don't have to worry about that
- If you need more control over the behaviour of the parser or just don't like the `ResultBuilder`, you only need to implement the `Philiagus\Parser\Contract\Parser`-Interface to be interoperable with other parsers. Just be aware that you MUST
  - create a `ParserBegin` subject when you enter the parser, wrapping the provided subject into another subject to ensure that the value chain is upheld
  - respect the `throwOnError()` info of the subject: If an error occurs and `throwOnError()` is active, you have to throw the Error (most times using `$error->throw()`). Accordingly, if `throwOnError()` is not active you have to add the Errors to the Result object.
