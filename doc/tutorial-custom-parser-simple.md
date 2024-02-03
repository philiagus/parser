# Tutorial: Simple Custom Parser

Let's say you want to write a simple parser that adds 1 to a provided number. Let's call this an `Increment` parser.

You would first start by creating the corresponding base class extending the Base Parser.

```php
<?php
declare(strict_types=1);

namespace Your\Namespace;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Contract;
use Philiagus\Parser\ResultBuilder;

class Increment extends Parser
{
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        // TODO: Implement execute() method.
    }

    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        // TODO: Implement getDefaultParserDescription() method.
    }
}
```

From the get-go you are provided with two methods you must implement. `execute` and `getDefaultParserDescription`.

## Parser description (implementing `getDefaultParserDesription`)

Let's look at `getDefaultParserDesription` first. It is used to provide a name for the parser. On error the parsers provide the sequence of actions that were taken to get to that error and one of these actions was using the defined parser, so this is the name that shows up in the generated sequence.

For this parser we'll simply implement this as follows:

```php
<?php
// ...

class Increment extends Parser {
    
    // ...
    
    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'increment';
    }
}
```

You can use the `$subject` provided to create a more meaningful name but in most cases this is not needed.

## static constructor (implementing `static new`)

While it is not mandatory, it is considered good form to implement static constructors for the parser. It is simply more convenient to write

```php

use Philiagus\Parser\Parser\ConvertToInteger;
use Philiagus\Parser\Parser\AssertInteger;

ConvertToInteger::new()
->then(AssertInteger::new()->assertMinimum(1))
```

compared to

```php
use Philiagus\Parser\Parser\ConvertToInteger;
use Philiagus\Parser\Parser\AssertInteger;

(new ConvertToInteger())
->then((new AssertInteger())->assertMinimum(1))
```

(not the brackets around the `new` instantiation).

It is also best practice to make any constructor private and allow creation of the parser only through static methods. This makes it easier and more readable to preload parser instances with a specific purpose on construct with different signatures.

As an example you can look at the `ConvertToArray` Parser which offers the static constructors `ConvertToArray::usingCast` and `ConverToArray::creatingArrayWithKey($key)`. Stuffing both of those into a constructor would yield the signature `__construct(true|int|string $castOrKey)` or even worse `__construct(bool $useCast, null|int|string $key = null)`, which are hard to read. `new ConvertToArray(true)` is not as readable as `ConverToArray::usingCast()`.

So we add the following to our Parser:

```php
<?php
declare(strict_types=1);

// ...

class Increment extends Parser
{

    private function __construct()
    {
        // prevent creation of instances from outside this parser
    }
    
    public static function new(): static
    {
        return new static();
    }
    
    // ...
}
```

We use `statc` as typehint and for instantiation so that classes extending `ConverToUpperCase` don't have to re-implement the corresponding methods.

## Parser functionality (implementing `execute`)

The execute method is the one that is actually doing the heavy lifting. When the parser is executed, the execute method is called with a `ResultBuilder` object.

For more information on the `ResultBuilder` please refer to it's [Documentation](./result-builder.md).

For now, we'll simply implement the functionality.

### Base functionality

As a start, we must receive the currently to be parsed value from the `$builder`. This is done using the `$builder->getValue()` method, which returns whatever is currently provided to the parser.

Then we can use that value to uppercase it (using `mb_strtoupper` in this example) and then we return the result by creating the corresponding result using the `$builder->createResult($result)` method.

```php
<?php
// ...

class Increment extends Parser {
    
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        // extract value from builder
        $value = $builder->getValue();

        // increment value by 1
        $result = $value + 1;

        // return success result
        return $builder->createResult($result);
    }
    
    // ...
}
```

**Testing first implementation**

We can simply test the current implementation by running the parser:

```php

use Philiagus\Parser\Base\Subject;

$result = \Your\Namespace\Increment::new()
    ->parse(Subject::default(3))
    ->getValue();

echo $result;
```

Running this code will result in exactly what we want:
```text
4
```

But now it's time to think about edge cases and errors that might creep up.

### Implementing error handling in `execute`

Before you keep on reading this tutorial, take a moment to think about potential edge-cases and problems the parser we have written so far might have. I'll do the same, and we can compare notes!

You found some?

Nice! I got the same! What if the provided value is _not_ numeric? To test for this error, we can use the following test-code:

```php

use Philiagus\Parser\Base\Subject;

$result = \Your\Namespace\Increment::new()
    ->parse(Subject::default('BANG')) // <- using a non-numeric string as subject
    ->getValue();

echo $result;
```

Executing this code results in the following:

```text
Fatal error: Uncaught TypeError: Unsupported operand types: string + int in /app/Increment.php on line XX

TypeError: Unsupported operand types: string + int in /app/Increment.php on line XX
```

So now we have to come up with a solution to this issue. Suggestion: Disallow anything that is not an integer or a float value. If a String is supposed to be allowed some other parser can take care of converting that to `int|float`.

There are two ways to go about this:
1. Extend the `AssertNumber` parser
2. Call the `AssertNumber` parser in our execute - essentially handing over the heavy lifting somewhere else.
3. Do the assertion ourselves and provide a meaningful error message

As each case come with certain edge-cases and as this is a tutorial, I'll present all of them here. But please note, that you should put serious thought into how you want to implement this and what it means for your environment, as both have different implications for performance and your knowledge requirements.

A short comparison of implications of the various approaches.

|                                       | Extends `AssertNumber`                               | Call `AssertNumber`                      | own implementation                                          |
|---------------------------------------|------------------------------------------------------|------------------------------------------|-------------------------------------------------------------|
| Cost on performance                   | **Middle**                                           | **High**: calling + instantiating Parser | As **low** as you can get it                                |
| Chance of missing something           | **low**: solid assertion built in                    | **Low**: solid assertion built in        | **Unclear**: How much do _you_ know about floats? Inf? NaN? |
| Allows custom "type error" message?   | **Yes**                                              | **No** (possible with a bit of work)     | **Yes**                                                     |
| Muddies public `Increment` signature? | **Yes**  (allows for number assertions like min/max) | **No**                                   | **No**                                                      |


But first, an excursion:

#### Excursion: Parsers and errors

The first version of the parsers were written to always throw an exception if an error occurred and all was well. But starting with `v2` the parsers allowed for accumulation of errors. Let's say you want to validate for an array of strings and two of the elements are integers. Sometimes you want to abort at the first number that creates an error - which is the default approach. But sometimes you want to accumulate the errors and provide all of them at once. This means that the parsers might no longer abort on the first exception they encounter.

This is one of the reasons why the parsers don't simply return the result of the parsing (such as in our case "the number + 1"), but instead a `Result` object that contains the result or the errors. Think of this like the `Result` in Rust which can be either `Ok` or `Err`.

But this behaviour depends on the `Subject`! The signature of the `Subject::default` method looks as follows:

`Subject::default(mixed $value, ?string $description = null, bool $throwOnError = true)`

For a better description on these parameters, please look at the [Subject Documentation](./subject.md). For this chapter, we are mainly looking at the `$throwOnError` parameter. If `$throwOnError` is true, the first error _should_ abort the Parsers. A `ParsingException` is thrown and can be caught at top level. We call this mode `throw mode`.

If `$throwOnError` is explicitly set to `false`, the parsers _should_ never throw a `ParsingException` and instead return the `Result` with either the successful result value _or_ the list of errors encountered during parsing. We call this mode `gather mode`.

To make this complexity somewhat manageable the `ResultBuilder` handles all this complexity in its exposed methods. You can later look at the [ResultBuilder Documentation](./result-builder.md), but for now this is all you need to know.

So let's return to our two options, starting with an attempt at implementing the first:

#### Option 1: Excend `AssertNumber`

```php
class Increment extends AssertNumber
{

    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        // use AssertNumber parser
        $numberResult = parent::execute($builder);

        // on error do not continue this parser
        if ($numberResult->hasErrors()) {
            return $numberResult;
        }

        // use number and increment by 1
        $result = $numberResult->getValue() + 1;

        // return success result
        return $builder->createResult($result);
    }

    #[\Override] protected function getDefaultParserDescription(Contract\Subject $subject): string
    {
        return 'increment';
    }
} 
```

The main changes to our code outside the `exceute` method: We extend `AssertNumber` instead of the `Base\Parser` and we removed the private constructor and static `new` (as both of those are implemented by AssertNumber).

Now lets got through the lines of `execute`:

```php
// use AssertNumber parser
$numberResult = parent::execute($builder);
```

First we must execute the `AssertNumber` functionality to ensure that the value we got is actually a number. The execute method _always_ results in a `Result` object. So we can use that in the following code to our advantage.

If we are in `throw mode` and the input is not a valid number, the code would never step over this line. `parent::execute($builder)` would throw a `ParsingException` and code-flow would be out of this parsers hands.

This means we are either in `gather mode` _or_ we have a number.

```php
// on error do not continue this parser
if ($numberResult->hasErrors()) {
    return $numberResult;
}
```

So we obviously check this next. If the result is an error, there is no reason to continue code execution. We simply return that result and let whoever called the parser deal with the error.

After this if we _know_ that `$numberResult` is a success result _and_ contains a number. So the following code can simply do its job as it used to (just using the value in `$numberResult` now, which souldn't be different from the `$builder` value anyway, as `AssertNumber` never changes the value).


#### Option 2: Call `AssertNumber`

```php
<?php
// ...

class Increment extends Parser {
    
    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        // use AssertNumber parser
        $numberResult = AssertNumber::new()->parse($builder->getSubject());

        // incorporate result of AssertNumber and use its result value
        $numberValue = $builder->unwrapResult($numberResult);

        // on error do not continue this parser
        if($builder->hasErrors()) {
            return $builder->createResultUnchanged();
        }

        // increment value by 1
        $result = $numberValue + 1;

        // return success result
        return $builder->createResult($result);
    }
    
    // ...
}
```

Let's walk through the code line by line

```php
// use AssertNumber parser
$numberResult = AssertNumber::new()->parse($builder->getSubject());
```

I imagine that this line is relatively straightforward and would most likely be what you would have typed yourself when implementing this approach. We create a new `AssertNumber` parser and hand the current subject over to that parser. In `throw mode` the `AssertNumber` Parser throws an exception on error and our job is done. In `gather mode` or if there is no error, we must do some more steps.

```php
// incorporate result of AssertNumber and use its result value
$numberValue = $builder->unwrapResult($numberResult);
```

Next we use the `$builder->unwrapResult` method. This method takes a result from another Parser and unwraps it. If `$numberResult` has errors, the builder incorporates those errors into its list of errors and the return value will be `null`. If `$numberResult` is successful, the return of `unwrapResult` will be the `int|float` we expect (with the added bonus of not being `NAN`, `INF` or `-INF`).

This means that we are now in the state of the `$builder` containing an error _or_ `$numberValue` containing our expected number.

```php
// on error do not continue this parser
if($builder->hasErrors()) {
    return $builder->createResultUnchanged();
}
```

If the `$builder` contains errors after unwrapping the `$numberValue` this means we are in `gather mode` and cannot continue.

In this case, we simply return the unchanged builder result. It actually wouldn't matter which `createResult...` method of the `$builder` we'd use, as any result would be in error mode. We simply use `createResultUnchanged`, because it doesn't need any parameters and it is the most reasonable solution: "If there is an error the provided value has not changed"

After his little `if`, we know that - no matter if `gather mode` or `throw mode` is active - `$numberResult` contains a valid number we can increment by 1, so our previous implementation can use that number, increment it by 1 and build that result.


#### Option 3: Custom assertion

```php
// ...

class Increment extends Parser
{

    use OverwritableTypeErrorMessage;

    #[\Override] protected function execute(ResultBuilder $builder): Contract\Result
    {
        // extract value from builder
        $value = $builder->getValue();

        // check that value is a valid number
        if(!is_int($value) && (!is_float($value) || is_nan($value) || is_infinite($value))) {
            // log type error
            $this->logTypeError($builder);

            // return if gather mode
            return $builder->createResultUnchanged();
        }

        // increment value by 1
        $result = $value + 1;

        // return success result
        return $builder->createResult($result);
    }

    #[\Override] protected function getDefaultTypeErrorMessage(): string
    {
        return 'Provided value is not of float or integer';
    }

    // ...
}
```

As type assertions (and errors thereof) are a common issue when writing parsers the package comes with a default trait `OverwritableTypeErrorMessage` for ease of usage.

The trait requests an abstract method `getDefaultTypeErrorMessage` from the implementation. The string provided there provides the default exception message to use for type errors. But the trait also exposes public `setTypeErrorMessage` method, so that any user of the parser can change the error message on type error to a custom string.

```php
// check that value is a valid number
if(!is_int($value) && (!is_float($value) || is_nan($value) || is_infinite($value))) {
    // log type error
    $this->logTypeError($builder);

    // return if gather mode
    return $builder->createResultUnchanged();
}
```

Here we make sure that our value is actually a number - so an integer or a float that is not Infinite or NaN.

If the value violates our rules, we simply use the `logTypeError` method of the `OverwriteableTypeErrorMessage` trait to set that error in the `$builder`.

On `throw mode` that line simply throws the error, but in `gather mode` we still have to return a result. So as in the other examples, we use `$builder->createResultUnchanged()` to create an unchanged result containing the error.

The rest of the code is actually the same.

### Testing

Parsers need good testing! But as this chapter is already long enough I suggest we move that to a [separate tutorial](./tutorial-testing-parsers.md)!

### Summary

Congratulations! You have implemented your first parser! Now go out there and enhance the ecosystem, build parser matching your need and mix-and-match them with the parsers of this bundle!

Your next reads for more information might include:
- more about the [ResultBuilder](./result-builder.md)
- more about the [Subject](./subject.md)
- how to write [Tests](./tutorial-testing-parsers.md)
