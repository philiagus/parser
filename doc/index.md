# Introduction

## Using Parsers

Parsers are an extendable suite of classes meant to assert, convert, sanitize and extract data from a provided dataset.

You can see a list of all parsers provided with this package here: [List of Parsers](./list-of-parsers.md)

The most important things to keep in mind are:

- all parsers have static constructors, so you are best supported if you simply type the name of the parser and let your IDE guide you in how to use it
- if you want to extract data while using a parser (see the example on the [Readme](../README.md)), use
  - `->thenAssignTo($variable)` in order to assign to a variable
  - `->thenAppendTo($array)` in order to append to an `array|\ArrayAccess`

## Why parsers?

While working in APIs and complex JSON structures the need to make the expected structure clearly parsed and defined became apparent. The desire was to define a code structure that looked like the JSON, but could also directly assert, convert and extract its data.

### Example 1: Structure checking

In a simple example, lets assume a JSON that looks like this:

```JSON
{
    "characters": [
        {
            "id": 7,
            "name": "Frodo",
            "task": "Carry the ring and destroy it"
        },
        {
            "id": 19,
            "name": "Sam",
            "task": "Support frodo"
        },
        {
            "id": 99,
            "name": "Saruman",
            "task": "Show that a machine mind is easily corrupted"
        }
    ]
}
```

The instructions for this structure could be written as such:

> A JSON string, containing an object with a key `characters` which in turn contains an array. That array of characters is defined as a list of objects, each having a key `id` (positive integer), `name` (non-empty UTF-8 string with human-readable characters) and `task` (rule is the same as `name`)


So in parsers, this might be written as:

```php

use \Philiagus\Parser\Parser\Parse\ParseJSONString;
use \Philiagus\Parser\Parser\Assert\AssertStdClass;
use \Philiagus\Parser\Parser\Assert\AssertArray;
use \Philiagus\Parser\Parser\Assert\AssertInteger;
use \Philiagus\Parser\Parser\Assert\AssertStringMultibyte;

// A JSON string,
ParseJSONString::new()              
    ->then(
        // containing an object
        AssertStdClass::new()
            // with a key `characters`
            ->givePropertyValue(
                'characters',
                // which in turn contains an array.
                AssertArray::new()
                    // That array of characters is defined as a list
                    // [optional as arrays in JSON are always sequential]
                    ->assertSequentialKeys() 
                    // of 
                    ->giveEachValue(    
                        // objects, each having
                        AssertStdClass::new()
                            // a key `id` (positive integer),
                            ->givePropertyValue('id', AssertInteger::greaterThan(0)) //
                            // `name` (non-empty UTF-8 string with human-readable characters)
                            ->givePropertyValue(
                                'name',
                                AssertStringMultibyte::UTF8()
                                    ->assertRegex('/^\P{C}++$/u')
                            )
                            // and `task` (rule is the same as `name`)
                            ->givePropertyValue(
                                'task',
                                AssertStringMultibyte::UTF8()
                                    ->assertRegex('/^\P{C}++$/u')
                            )
                    )
            )
    )
    ->parse(Subject::default($json));
```

The things this parser checks are the following:
- The String is a valid JSON
- The JSON contains an object (`\stdClass` in PHP)
- That object has a property `characters`
- That property contains an array
- That array contains a list of objects
- Each of these objects contains a property `id`
- The `id` property is an integer greater than 0
- The objects also contain a property `name`
- The `name` property contains a UTF-8 compatible string (ASCII is UTF-8 compatible), that adheres to the provided regular expression (no control characters, so also no newlines)
- The objects also contain a property `task`
- The `task` property contains a UTF-8 compatible string (ASCII is UTF-8 compatible), that adheres to the provided regular expression (no control characters, so also no newlines)

If any of these assertions fail the parser will throw an exception, providing information on what failed, where it failed and what was expected.

### Example 2: Extraction

In a different example, lets have a structure like this:

```json

{
    "firstname": "Andreas",
    "lastname": "Eicher"
}
```

and a definition as

> The value is provided as a JSON with two elements: firstname and lastname, both of which are not empty, not control character UTF-8 strings.


```php
use \Philiagus\Parser\Parser\Parse\ParseJSONString;
use \Philiagus\Parser\Parser\Assert\AssertStdClass;

ParseJSONString::new()
    ->then(
        AssertStdClass::new()
            ->givePropertyValue(
                'firstname',
                AssertStringMultibyte::UTF8()
                    ->assertRegex('/^\P{C}++$/u')
                    ->thenAssignTo($firstname)
            )
            ->givePropertyValue(
                'lastname',
                AssertStringMultibyte::UTF8()
                    ->assertRegex('/^\P{C}++$/u')
                    ->thenAssignTo($lastname)
            )
    )
    ->parse(Subject::default($json));
```

After this parser has been executed the variables `$firstname` and `$lastname` are filled with the values from the JSON. Please be aware that `$firstname` might get set even if an error is raised. When for example this JSON is provided `{"firstname":"Bob"}`, the parser checking the firstname will succeed and assign, but the one checking for the `lastname` will fail (for the property being not present) and the parser will abort with an exception.
    
## Writing Parsers

- [Tutorial: Writing parsers using Base\Parser as basis](./tutorial-custom-parser-simple.md)
- [What the ResultBuilder does](./result-builder.md)
- [What is a Subject](./subject.md)

There are also a few fundamental design rules:
- The action of executing a parser _should not_ alter the internal state of the parser. The behaviour of a parser instance _should not_ change, no matter how often that parser is executed. There should be no internal state needed. If the parser needs to remember something, the `Subject->getMemory`/`Subject->getMemory`/`Subject->hasMemory` methods should be used.
- Parsers are configured using fluent methods, whose naming follows this pattern:
  - **Sequence control methods** are methods that define the order in which to do things in the parser. Any call to these methods must add another thing to do in the parsers internal list of commands. This can be achieved by adding the "things to do" to an array and looping through that array when executing the parser
    - `assert*`: Configures a rule that is asserted

      **Example**: `AssertInteger->assertMinimum` asserting the value to be greater or equal to the provided value 
    - `give*`: Hand an information to another parser but ignore its result on success, treading the value as immutable.
     
      **Example**: `AssertArray->giveValue` provides the value of an array key to the other parser, but even if that parser changes that value, the original value in the parsed array does not change
    - `modify*`: Hands the specified information to another parser and changes the value based on the parsers result.
      
      Example: Such as `ParseArray->modifyValue`, which provides the value of an array key to another parser and changes the value of that key to the parsers result in its result.
  - **Configuration methods** are methods that change configuration of the parser:
    - `set*`: Sets a configuration of the parser. Every new call to the same `set*` method overwrites and sometimes set methods overwrite each other (such as the `AssertInfinite` parser allowing to `setAssertPositive` and `setAssertNegative`)
  - **Chaining methods** are methods that cause other parsers/events to happen after the current parser succeeded. Any such chain always breaks at the first parser to result in an error.
    - `then*`: Chains the thing to be executed after the current parser, acting on the current parsers result.
