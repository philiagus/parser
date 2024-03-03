# Result Builder

The `\Philiagus\Parser\ResultBuilder` is a class used inside parsers for ease of interaction with the expected behaviour of parsers (related to error modes and the like).

It is recommended to read the [Tutorial: Simple Custom Parser](./tutorial-custom-parser-simple.md) before diving into the ins and outs of the ResultBuilder.

## What does it do?

The ResultBuilder is created by the Base Parser and handed to the protected method defined by the base parser.

```php
declare(strict_types=1);

namespace YourNamespace;

use Philiagus\Parser\Base\Parser;
use Philiagus\Parser\Base\Parser\ResultBuilder;
use Philiagus\Parser\Contract;

class YourParser extends Parser {

    // BEHOLD! A ResultBuilder being injected ⬇⬇⬇⬇⬇⬇⬇⬇⬇
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

Then you can use the ResultBuilder from within the Parser to easily handle most use cases. It wraps around the provided Subject (and creates subjects itself) for ease of parser use.

## Common cases

### Receiving/Changing the currently parsed Value

The _Value_ being parsed is different from the _Subject_ being parsed in that way that the _Value_ is a field within the _Subject_. The _Subject_ contains more Info (such as: Shall we throw exceptions? Where in the parser chain are we currently? etc...). The _Value_ is the real thing in memory we are supposed to parse (the JSON string or Array or whatever).

The easiest way to receive the current value from the ResultBuilder is `$builder->getValue()`. From the get-go this value is the same as `$builder->getSubject()->getValue()`, but it might change.

The change is due to the fact that you can use the `$builder->setValue($description, $value)` method to change the value inside the Builder. This is commonly used in complex parsers (such as the `ParseArray` parser) to overwrite the currently parsed value (example: after adding a key to the array). This method also automatically wraps the subject into a utility subject, as only subjects can be provided to other parsers, subjects are used to build results and the received subjects value is immutable.

But - long story short - no matter where in the Parser the `getValue()` method is used, the method will always return the current thing to be parsed (event if you altered it yourself).

### Handling error cases

Error cases come in different shapes and sizes. Generally speaking the system can be in two states: `gather mode` and `throw mode`. In `gather mode` Errors are accumulated and returned as a list of errors, in `throw mode` the first Error is thrown as a `ParsingException` as an "exit early"-strategy.

The ResultBuilder handles these details for your convenience, providing multiple methods.

- `logError` adds a provided Error object to the list of errors or throws it directly if `throw mode` is enabled
- `logErrorStringify` creates a new Error object internally, using the `Stringify` class to build a helpful Error message

No matter which of the two methods you use, both might _not_ stop your code flow (given that in `gather mode` they do not throw a `ParsingException`).

This means that in most cases you would call `return $builder->createResultUnchanged();` directly after any of the two methods.

Only in cases where you are sure that later code should be executed as well you may forego this rule. An example would be the `AssertArray` parser, which gathers all errors it can get, thus having the possibility to hint towards multiple missing keys or multiple violations.

So (generally speaking) your code should in most cases look like this:

```php
if (my_breaking_check) {
    $builder->logErrorStringify('Your error message'); // align this call as you please
    
    return $builder->createResultUnchanged(); // the easiest way to create a result but as you
        // already know that it will contain an error the result value is of little relevance
        // to the created result object
}
```

### Creating a result object

Every parser must always return a Result object. The `ResultBuilder` is named after that, revealing its main purpose in life.

It offers multiple methods to create a result, but for all cases it will always attach any gathered/logged Errors to the Result object.

This means that the Result object can only be treated as success, if the `ResultBuilder` has not found any errors, which is exactly what we want.

- `createResult(mixed $resultValue)`: Creates a Result object with the provided `$resultValue` as its value
- `createResultFromResult(Result $result)`: Create a result, forwarding the result of another parser as its value. This is most times needed when your parser delegates the last step of its job to another parser and thus the result of that parser is the result of your parser.
- `createResultUnchanged()`: Creates the Result object with its value being the unchanged value that the parser received. This is used in all `Assert*`-Parsers and also in most cases when `return` is called directly after logging an error in order to exit when in _gather mode_.
- `createResultWithCurrentValue()`: Creates the Result object with the current value stored in the `ResultBuilder`. You can overwrite the current value in the `ResultBuilder` by using its `setValue`-method as described before.
